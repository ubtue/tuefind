[![CI Status](https://github.com/ubtue/vufind-collapse-expand/actions/workflows/ci.yaml/badge.svg?branch=main)](https://github.com/ubtue/vufind-collapse-expand/actions/workflows/ci.yaml)

# VuFindCollapseExpand module for VuFind

This module offers a simple and performant but not perfect way to recognize and group seemingly duplicate records. It uses [Apache Solr's Collapse and Expand Results](https://solr.apache.org/guide/solr/9_8/query-guide/collapse-and-expand-results.html).

## Enabling the module

Here is the step by step to enable this module:

1. Add the following line to `composer.json`:
   "ubtue/vufind-collapse-expand": "@dev"

2. Update composer package (via terminal):
   `composer update`

3. Add the module to `application.config.php` in the config folder.

   ```
   $module = [
   ...
   'VuFindCollapseExpand',
   ...
   ]
   ```

   For advanced users, it's also possible to copy the into `modules` and enable it in `httpd-vufind.conf`.
   But then you need to care about updates yourself.

4. Add the trait
   In your record driver `RecordDriver/SolrDefault` (ex. RecordDriver/SolrDefault.php), use the `CollapseExpandTrait` by adding

   ```php
   class SolrDefault extends \VuFind\RecordDriver\SolrMarc implements \VuFindCollapseExpand\Config\CollapseExpandConfigAwareInterface
   {
       ...
       use \VuFindCollapseExpand\RecordDriver\Feature\CollapseExpandTrait;
       use \VuFindCollapseExpand\Config\CollapseExpandConfigAwareTrait;
       ...
   }
   ```

5. Add the config to `config.ini`

   ```ini

   ; The mandatory fields are collapse.field, expand.field, and expand.rows. It is better to set the same value for collapse.field and expand.field.
   ; When the collapse.field is set, the feature is active.
   ; If you want to override defaults / use specific features, please have a look at the Solr Documentation:
   ; https://solr.apache.org/guide/solr/latest/query-guide/collapse-and-expand-results.html
   ; collapse
   ; mandatory fields are collapse.field, expand.field and expand.rows. The collapse.field is recommended to set the same value with expand.field
   [CollapseExpand]
   collapse.field = title_sort
   ;collapse.min =
   ;collapse.max =
   ;collapse.sort =
   ;collapse.nullPolicy = ignore
   ;collapse.hint =
   ;collapse.size = 100000
   ;collapse.collectElevatedDocsWhenCollapsing = true

   expand.field = title_sort
   expand.rows = 500
   ;expand.sort = score desc
   ;expand.q =
   ;expand.fq =
   ;expand.nullGroup = false
   ```

6. User Interface - HTML
   **Mixin**

   Create a symlink or copy `res/theme` to `themes/collapse_expand_mixin` and register the mixin in your `theme.config.php`:
   `'mixins' => ['collapse_expand_mixin']`

   **Checkbox**

   Add a reference in your search/results.phtml to the result-list-snippet.phtml
   Copy the code in the file `res/theme/templates/search/controls/collapse_expand.html` where you want the checkbox for enabling/ disabling CollapseExpand dynamically, for example in `[your_theme]/templates/search/results.html`

   **Result list**

   Add a reference in your result-list.phtml to the result-list-snippet.phtml
   `<?=$this->render('RecordDriver/DefaultRecord/result-list-snippet.phtml')?>`

   **Record Tab**

   CollapseExpand comes with a record tab called `Other Document` to show the expand documents when user access the detail information of the record. Using the feature is simple, just follow the instruction below to activate.

   `RecordTabs.ini` (`config/vufind/RecordTabs.ini`)

   ```ini
   [VuFind\RecordDriver\SolrMarc]
   ...
   tabs[CollapseExpand] = CollapseExpand
   ...
   ```

   **Language Translation**

   Adding the translation into `[language].ini` for example the english translation:

   ```ini
   ...
   group hits = "Group similar items"
   show_grouped_items = "Show grouped items"
   ```

## Enabling the VuFindCollapseExpand module along custom code modules

The VuFindCollapseExpand module extends several VuFind classes. Therefore, if you have added a module with custom code to your VuFind installation which customizes any of the following classes you need to list the VuFindCollapseExpand module in the `application.config.php` prior to your custom module and alter the inheritance references to the VuFindCollapseExpand module accordingly.

VuFind classes extended in VuFindCollapseExpand module:

```php
\VuFind\AjaxHandler\AbstractBase
\VuFind\Controller\SearchController
\VuFind\Search\Factory\AbstractSolrBackendFactory
\VuFind\Search\Solr\Params
\VuFind\ServiceManager\ServiceInitializer
\VuFindSearch\Backend\Solr\Backend
\VuFindSearch\Backend\Solr\Response\Json\RecordCollection
```
