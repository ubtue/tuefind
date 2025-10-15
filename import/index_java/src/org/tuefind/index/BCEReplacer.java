package org.tuefind.index;

import java.util.ArrayList;
import java.util.Collections;
import java.util.List;
import java.util.Map;
import java.util.regex.Matcher;
import java.util.regex.Pattern;
import java.util.HashMap;

/** \class BCEReplacer
 *  \brief Provides mapping from non-standard German BCE references to standard German BCE references.
 */
public class BCEReplacer {
    Pattern pattern;
    String replacement;
    private BCEReplacer(final String pattern, final String replacement) {
        this.pattern = Pattern.compile(pattern);
        this.replacement = replacement;
    }

    private static Map<String, String> lang_to_bc_expressions;
    private static Map<String, List<BCEReplacer>> bce_replacement_maps;

    static {
       bce_replacement_maps = new HashMap<>();
       lang_to_bc_expressions = Collections.unmodifiableMap(Map.of(
            "de", "v. Chr.",
            "en", "BC",
            "fr", "avant J.-C.",
            "es", "a. C.",
            "it", "a.C.",
            "pt", "a.C.",
            "ru", "до н.э.",
            "el", "π.Χ."
       ));

       // Non-standard BCE year references and their standardized replacements. Backreferences for matched groups look like $N
       // where N is a single-digit ASCII character referencing the N-th matched group.
       for (String lang : lang_to_bc_expressions.keySet()) {
           final List<BCEReplacer> tempList = new ArrayList<>();
           final String bc_expression = lang_to_bc_expressions.get(lang);
           tempList.add(new BCEReplacer("v(\\d+) ?- ?v(\\d+)", "$1 " + bc_expression + "-$2 " + bc_expression));
           tempList.add(new BCEReplacer("v(\\d+) ?- ?(\\d+)", "$1 " + bc_expression + "-$2"));
           tempList.add(new BCEReplacer("v(\\d+)", "$1 " + bc_expression));
           bce_replacement_maps.put(lang, Collections.unmodifiableList(tempList));
       }
    }


    /** \return If the regex matched all matches will be replaced by the replacemnt pattern o/w the original
        "subject" will be returned. */
    private String replaceAll(final String subject) {
        final Matcher matcher = this.pattern.matcher(subject);
        return matcher.replaceAll(this.replacement);
    }



    protected static List<BCEReplacer> getBCEReplacementMap(final String lang) {
        if (bce_replacement_maps.containsKey(lang))
            return bce_replacement_maps.get(lang);
        return bce_replacement_maps.get("en");
    }


    public static String replaceBCEPatterns(final String s, final String lang) {
        for (final BCEReplacer regex_and_replacement : getBCEReplacementMap(lang)) {
            final String patchedString = regex_and_replacement.replaceAll(s);
            if (!patchedString.equals(s))
                return patchedString;
        }

        return s;
    }


    // Replaces all occurences of the first match found in bce_replacement_map, or returns the original string if no matches were found.
    public static String replaceBCEPatterns(final String s) {
        return replaceBCEPatterns(s, "de");
    }
}
