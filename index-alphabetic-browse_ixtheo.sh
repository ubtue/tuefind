#!/bin/bash
set -o errexit

# Setup ramdisk to speed up things

TMP_RAMDISK_DIR="/tmp/ramdisk"

# We are ordinarily run a solr and need an appropriate sudoers setup c.f. sudoers.d/99-alphabrowse_index_ramdisk
[ ${USER} == "solr" ] && mount_command="sudo mount" || mount_command="mount"
[ ${USER} == "solr" ] && umount_command="sudo umount" || umount_command="umount"

trap ExitHandler EXIT
trap ExitHandler SIGINT

function ExitHandler {
   ShutdownRamdisk
}

function ShutdownRamdisk() {
    if mountpoint --quiet ${TMP_RAMDISK_DIR}; then
        ${umount_command} ${TMP_RAMDISK_DIR}
    fi
}


#####################################################
# Build java command
#####################################################
if [ "$JAVA_HOME" ]
then
  JAVA="$JAVA_HOME/bin/java"
else
  JAVA="java"
fi


##################################################
# Set VUFIND_HOME
##################################################
if [ -z "$VUFIND_HOME" ]
then
  # set VUFIND_HOME to the absolute path of the directory containing this script
  # https://stackoverflow.com/questions/4774054/reliable-way-for-a-bash-script-to-get-the-full-path-to-itself
  VUFIND_HOME="$(cd "$(dirname "$0")" && pwd -P)"
  if [ -z "$VUFIND_HOME" ]
  then
    exit 1
  fi
fi


if [ -z "$SOLR_HOME" ]
then
  SOLR_HOME="$VUFIND_HOME/solr/vufind"
fi

# This can point to an external Solr in e.g. a Docker container
if [ -z "$SOLR_JAR_PATH" ]
then
  SOLR_JAR_PATH="${SOLR_HOME}/../vendor"
fi

set -e
set -x

cd "`dirname $0`/import"
SOLRMARC_CLASSPATH=$(echo solrmarc_core*.jar)
if [[ `wc -w <<<"$SOLRMARC_CLASSPATH"` -gt 1 ]]
then
  echo "Error: more than one solrmarc_core*.jar in import/; exiting."
  exit 1
fi
CLASSPATH="browse-indexing.jar:${SOLRMARC_CLASSPATH}:${VUFIND_HOME}/import/lib/*:${SOLR_HOME}/jars/*:${SOLR_JAR_PATH}/modules/analysis-extras/lib/*:${SOLR_JAR_PATH}/server/solr-webapp/webapp/WEB-INF/lib/*"



mkdir -p ${TMP_RAMDISK_DIR}
if ! mountpoint --quiet ${TMP_RAMDISK_DIR}; then
   ${mount_command} -t tmpfs -o size=10G tmpfs ${TMP_RAMDISK_DIR}
fi

# make index work with replicated index
# current index is stored in the last line of index.properties
function locate_index
{
    local targetVar=$1
    local indexDir=$2
    # default value
    local subDir="index"

    if [ -e $indexDir/index.properties ]
    then
        # read it into an array
        readarray farr < $indexDir/index.properties
        # get the last line
        indexline="${farr[${#farr[@]}-1]}"
        # parse the lastline to just get the filename
        subDir=`echo $indexline | sed s/index=//`
    fi

    eval $targetVar="$indexDir/$subDir"
}

locate_index "bib_index" "${SOLR_HOME}/biblio"
locate_index "auth_index" "${SOLR_HOME}/authority"
index_dir="${SOLR_HOME}/alphabetical_browse"

mkdir -p "$index_dir"

function build_browse
{
    browse=$1
    field=$2
    skip_authority=$3

    extra_jvm_opts=$4
    filter=$5

    [[ ! -z $filter ]] && browse_unique=${TMP_RAMDISK_DIR}/${browse}-${filter} || browse_unique=${TMP_RAMDISK_DIR}/${browse}

    # Get the browse headings from Solr
    if [ "$skip_authority" = "1" ]; then
        if ! output=$($JAVA ${extra_jvm_opts} -Dfile.encoding="UTF-8" -Dfield.preferred=heading -Dfield.insteadof=use_for -cp $CLASSPATH org.vufind.solr.indexing.PrintBrowseHeadings "$bib_index" "$field" "" "${browse_unique}.tmp" "$filter" 2>&1); then
            echo "ERROR: Failed to create browse headings for ${browse}. ${output}."
            exit 1
        fi
    else
        if ! output=$($JAVA ${extra_jvm_opts} -Dfile.encoding="UTF-8" -Dfield.preferred=heading -Dfield.insteadof=use_for -cp $CLASSPATH org.vufind.solr.indexing.PrintBrowseHeadings "$bib_index" "$field" "$auth_index" "${browse_unique}.tmp" "$filter" 2>&1); then
            echo "ERROR: Failed to create browse headings for ${browse}. ${output}."
            exit 1
        fi
    fi

    if [[ ! -z $filter ]]; then
        out_dir="$index_dir/$filter"
        mkdir -p "$out_dir"
        chown solr:solr $out_dir
    else
        out_dir="$index_dir"
    fi

    # Sort the browse headings
    if ! output=$(sort -T ${TMP_RAMDISK_DIR} -u -t$'\1' -k1 "${browse_unique}.tmp" -o "${browse_unique}_sorted.tmp" 2>&1); then
        echo "ERROR: Failed to sort ${browse}. ${output}."
        exit 1
    fi

    # Build the SQLite database
    if ! output=$($JAVA -Dfile.encoding="UTF-8" -cp $CLASSPATH org.vufind.solr.indexing.CreateBrowseSQLite "${browse_unique}_sorted.tmp" "${browse_unique}_browse.db" 2>&1); then
        echo "ERROR: Failed to build the SQLite database for ${browse}. ${output}."
        exit 1
    fi

    # Clear up temp files
    if ! output=$(rm -f *.tmp 2>&1); then
        echo "ERROR: Failed to clear out temp files for ${browse}. ${output}."
        exit 1
    fi

    # Move the new database to the index directory
    if ! output=$(mv "${browse_unique}_browse.db" "$out_dir/${browse}_browse.db-updated" 2>&1); then
        echo "ERROR: Failed to move ${browse}_browse.db database to ${out_dir}/${browse}_browse.db-updated. ${output}."
        exit 1
    fi

    # Indicate that the new database is ready for use
    if ! output=$(touch "$out_dir/${browse}_browse.db-ready" 2>&1); then
        echo "ERROR: Failed to mark the new ${browse} database as ready for use. ${error}."
        exit 1
    fi

    # tuefind specific:
    # set user of out file to solr, so if script is accidentally executed as root
    # the output files will be owned by solr user.
    # (else solr service can't import it)
    chown -R solr:solr "$out_dir"
}


function GenerateIndexForSystem {
    system_flag="$1"
    echo build_browse "hierarchy" "hierarchy_browse" 1 "" ${system_flag}
    time build_browse "hierarchy" "hierarchy_browse" 1 "" ${system_flag}
    echo build_browse "title" "title_fullStr" 1 "-Dbib_field_iterator=org.vufind.solr.indexing.StoredFieldIterator -Dsortfield=title_fullStr -Dvaluefield=title_fullStr -Dbrowse.normalizer=org.vufind.util.TitleNormalizer" ${system_flag}
    time build_browse "title" "title_fullStr" 1 "-Dbib_field_iterator=org.vufind.solr.indexing.StoredFieldIterator -Dsortfield=title_fullStr -Dvaluefield=title_fullStr -Dbrowse.normalizer=org.vufind.util.TitleNormalizer" ${system_flag}
    echo build_browse "topic" "topic_browse" 1 "" ${system_flag}
    time build_browse "topic" "topic_browse" 1 "" ${system_flag}
    echo build_browse "author" "author_browse" "" 1 ${system_flag}
    time build_browse "author" "author_browse" 1 "" ${system_flag}
    echo build_browse "lcc" "callnumber-raw" 1 "-Dbrowse.normalizer=org.vufind.util.LCCallNormalizer" ${system_flag}
    time build_browse "lcc" "callnumber-raw" 1 "-Dbrowse.normalizer=org.vufind.util.LCCallNormalizer" ${system_flag}
    echo build_browse "dewey" "dewey-raw" 1 "-Dbrowse.normalizer=org.vufind.util.DeweyCallNormalizer" ${system_flag}
    time build_browse "dewey" "dewey-raw" 1 "-Dbrowse.normalizer=org.vufind.util.DeweyCallNormalizer" ${system_flag}
}


GenerateIndexForSystem &
GenerateIndexForSystem "is_religious_studies" &
rm -f ${TMP_RAMDISK_DIR}/*.tmp
wait
GenerateIndexForSystem "is_biblical_studies" &
GenerateIndexForSystem "is_canon_law" &
wait
echo "Finished generating alphabrowse indices..."
