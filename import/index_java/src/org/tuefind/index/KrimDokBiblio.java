package org.tuefind.index;

import java.util.Arrays;
import java.util.ArrayList;
import java.util.Collection;
import java.util.List;
import java.util.Set;
import org.marc4j.marc.DataField;
import org.marc4j.marc.Record;
import org.marc4j.marc.Subfield;
import org.marc4j.marc.VariableField;

public class KrimDokBiblio extends TueFindBiblio {

    public String isAvailableForPDA(final Record record) {
        final List<VariableField> fields = record.getVariableFields("PDA");
        return Boolean.toString(!fields.isEmpty());
    }

    public String getFullTextElasticsearch(final Record record) throws Exception {
        return extractFullTextFromJSON(getFullTextServerHits(record), "" /* empty to catch all text types */);
    }

    public Set<String> getAllTopicsCloud(final Record record) {
        final Set<String> topics = getAllSubfieldsBut(record, "600:610:611:630:650:653:656:689a", "02");
        final List<String> excludeIndicators = Arrays.asList("rv", "bk");
        topics.addAll(getAllSubfieldsBut(record, "936a", "0", excludeIndicators));
        topics.addAll(getLocal689Topics(record));
        return topics;
    }

    public boolean HasLocalSelector(final Record record, String selector) {
        return getLocalDataAsRecords(record).stream().filter(lok_record -> {
            for (final VariableField variableField : lok_record.getVariableFields("935")) {
                DataField _LOK935field = (DataField) variableField;
                final Subfield _subfieldA = _LOK935field.getSubfield('a');
                if (_subfieldA != null && _subfieldA.getData().equals(selector))
                    return true;
            }
            return false;
        }).count() != 0;
    }

    public boolean HasFormalKeywordSelector(final Record record, String selector) {
        for (final String tag : new String[] { "655", "689" } ) {
            for (final VariableField variableField : record.getVariableFields(tag)) {
                 DataField _dataField = (DataField) variableField;
                 final Subfield _subfieldA = _dataField.getSubfield('a');
                 if (_subfieldA != null && _subfieldA.getData().equals(selector))
                     return true;
            }
        }
        return false;
    }

    public Collection<String> getKrimSpecialCollection(final Record record) {
        Collection<String> results = new ArrayList<>();
        if (HasLocalSelector(record, "kreb"))
            results.add("0/Albert Krebs Bibliothek/");
        if (getRecordSelectors(record).contains("rexa"))
+           results.add("0/Rechtsextremismus & Antisemitismus/");
        if (HasFormalKeywordSelector(record, "Statistik"))
            results.add("0/Statistiken/");
        if (HasFormalKeywordSelector(record, "Forschungsdaten"))
            results.add("0/Forschungsdaten/");
        return results;
    }
}
