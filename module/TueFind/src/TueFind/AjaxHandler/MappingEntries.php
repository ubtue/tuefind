<?php

namespace TueFind\AjaxHandler;

use Laminas\Mvc\Controller\Plugin\Params;
use SebastianBergmann\Environment\Console;
use VuFind\Db\Entity\UserEntityInterface;

class MappingEntries extends \VuFind\AjaxHandler\AbstractBase
{
    use \VuFind\I18n\Translator\TranslatorAwareTrait;

    protected $searchResultsManager;

    public function __construct(\VuFind\Search\Results\PluginManager $searchResultsManager, protected ?UserEntityInterface $user) {
        $this->searchResultsManager = $searchResultsManager;
    }
    /**
     * Handle a request.
     *
     * @param Params $params Parameter helper from controller
     *
     * @return array [response data, HTTP status code]
     */
    public function handleRequest(Params $params)
    {

        if ($params->fromQuery('action') === 'getPartners') {

            $group = $params->fromQuery('group');

            /* 
               At the moment we don't have any translations and we don't use variable $currentLang,
               but we want to have the possibility to use it in the future. 
            */

            $currentLang = $params->fromQuery('lang'); 

            $partnersArray = [
                // comm
                [   'lon' => '7.110015253718861',
                    'lat' => '50.72319649759073',
                    'group' => 'comm',
                    'popup' => '<div><font color="black"><p><a href="https://www.katholische-theologie.info/zusammenschl%C3%BCsse" target="_blank">Vereinigung der Arbeitsgemeinschaften für Katholische Theologie (VAKT)</a></p><p>c/o Sekretariat der Deutschen Bischofskonferenz <br /> Kaiserstr. 161 <br /> 53113 Bonn</p></font></div>',
                    'name' => 'Vereinigung der Arbeitsgemeinschaften für Katholische Theologie (VAKT)'
                ],
                [   'lon' => '13.310539926813998',
                    'lat' => '52.460591726851824',
                    'group' => 'comm',
                    'popup' => '<div><font color="black"><p><a href="https://wgth.de/" target="_blank">Wissenschaftliche Gesellschaft für Theologie (WGTh)</a></p><p>Haus der Diakonie<br />
                    Paulsenstrasse 55-56 <br />
                    12163 Berlin  </p></font></div>',
                    'name' => 'Wissenschaftliche Gesellschaft für Theologie (WGTh)'
                ],
                [   'lon' => '8.771853669066896',
                    'lat' => '50.80808118848182',
                    'group' => 'comm',
                    'popup' => '<div><font color="black"><p><a href="https://etft.de/" target="_blank">Evangelisch-Theologischer Fakultätentag</a></p><p>c/o Prof. Dr. Malte Dominik Krüger<br />
                    Theologische Fakultät<br />
                    Lahntor 3<br />
                    35032 Marburg</p></font></div>',
                    'name' => 'Evangelisch-Theologischer Fakultätentag'
                ],
                [   'lon' => '8.713223734379403',
                    'lat' => '50.09896744010327',
                    'group' => 'comm',
                    'popup' => '<div><font color="black"><p><a href="http://kthf.de/" target="_blank">Katholisch-Theologischer Fakultätentag</a></p><p>c/o Prof. Dr. Dirk Ansorge<br />
                    Offenbacher Landstraße 224<br />
                    60599 Frankfurt am Main</p></font></div>',
                    'name' => 'Katholisch-Theologischer Fakultätentag'
                ],
                [   'lon' => '12.427325055201944',
                    'lat' => '41.89718307461863',
                    'group' => 'comm',
                    'popup' => '<div><font color="black"><p><a href="https://www.chiesacattolica.it/" target="_blank">Conferenza Episcopale Italiana</a></p><p>Circonvallazione Aurelia, 50<br />
                    00165 Roma<br />
                    Italia</p></font></div>',
                    'name' => 'Conferenza Episcopale Italiana'
                ],
                [   'lon' => '6.102331538202423',
                    'lat' => '46.277782969097665',
                    'group' => 'comm',
                    'popup' => '<div><font color="black"><p><a href="https://globethics.net/" target="_blank">Globethics</a></p><p>Chemin du Pavillon 2<br />
                    1218 Le Grand-Saconnex Geneve<br />
                    Suisse</p></font></div>',
                    'name' => 'Globethics'
                ],
                [   'lon' => '9.05766754078338',
                    'lat' => '48.521684007647224',
                    'group' => 'comm',
                    'popup' => '<div><font color="black"><p><a href="https://www.ia-practicaltheology.org/" target="_blank">International Academy of Practical Theology (IAPT)</a></p><p>
                    Tübingen</p></font></div>',
                    'name' => 'International Academy of Practical Theology (IAPT)'
                ],
                [   'lon' => '11.3557404534526',
                    'lat' => '44.4942705145582',
                    'group' => 'comm',
                    'popup' => '<div><font color="black"><p><a href="https://www.resilience-ri.eu/" target="_blank">RESILIENCE (Religious Studies Infrastructure: Tools, Innovation, Experts, Connections and Centres in Europe)</a></p><p>Fscire
                    Via San Vitale, 114<br />
                    40125 Bologna<br />
                    Italia</p></font></div>',
                    'name' => 'RESILIENCE (Religious Studies Infrastructure: Tools, Innovation, Experts, Connections and Centres in Europe)'
                ],
                //Bibliographien
                [   'lon' => '9.931387440185757',
                    'lat' => '49.796449754346156',
                    'group' => 'bibliographien',
                    'popup' => '<div><font color="black"><p><a href="https://www.uibk.ac.at/bildi/" target="_blank">Biblische Literaturdokumentation Innsbruck (BILDI)</a></p><p>Karl-Rahner-Platz 1<br />
                    6020 Innsbruck<br />
                    Österreich
                    </p></font></div>',
                    'name' => 'Biblische Literaturdokumentation Innsbruck (BILDI)'
                ],
                [   'lon' => '9.931387440185757',
                    'lat' => '49.796449754346156',
                    'group' => 'bibliographien',
                    'popup' => '<div><font color="black"><p><a href="https://www.augustinus.de/" target="_blank">Augustinus-Bibliographie</a></p><p>Dominikanerplatz 4<br />
                    97070 Würzburg</p></font></div>',
                    'name' => 'Augustinus-Bibliographie'
                ],
                [   'lon' => '6.584317040044625',
                    'lat' => '46.52381424741675',
                    'group' => 'bibliographien',
                    'popup' => '<div><font color="black"><p><a href="https://www.unil.ch/irsb/fr/home/menuguid/bibil.html" target="_blank">Bibliographie biblique informatisée de Lausanne (BiBIL)</a></p><p>Institut romand des sciences bibliques<br />
                    Anthropole<br />
                    1015 Lausanne<br />
                    Suisse</p></font></div>',
                    'name' => 'Bibliographie biblique informatisée de Lausanne (BiBIL)'
                ],
                [   'lon' => '11.398208440076123',
                    'lat' => '47.269034873840944',
                    'group' => 'bibliographien',
                    'popup' => '<div><font color="black"><p><a href="https://www.uibk.ac.at/bildi/" target="_blank">Biblische Literaturdokumentation Innsbruck (BILDI)</a></p><p>Karl-Rahner-Platz 1<br />
                    6020 Innsbruck<br />
                    Österreich</p></font></div><div><font color="black"><p><a href="https://www.uibk.ac.at/praktheol/kaldi/" target="_blank">Kanonistische Literaturdokumentation Innsbruck (KALDI)</a></p><p>Institut für Praktische Theologie<br />
                    Karl-Rahner-Platz 1<br />
                    6020 Innsbruck<br />
                    Österreich</p></font></div>',
                    'name' => 'Biblische Literaturdokumentation Innsbruck (BILDI) - Kanonistische Literaturdokumentation Innsbruck (KALDI)'
                ],
                [   'lon' => '7.6236567979551655',
                    'lat' => '51.9626552214986',
                    'group' => 'bibliographien',
                    'popup' => '<div><font color="black"><p><a href="https://www.uni-muenster.de/FB2/ikr/datenbank/index.shtml" target="_blank">Datenbank Kanonisches Recht (DaKaR)</a></p><p>Institut für Kanonisches Recht<br />
                    Domplatz 23<br />
                    48143 Münster</p></font></div>',
                    'name' => 'Datenbank Kanonisches Recht (DaKaR)'
                ],
                //bibliotheken
                [   'lon' => '4.707481255577383',
                    'lat' => '50.86064339810748',
                    'group' => 'bibliotheken',
                    'popup' => '<div><font color="black"><p><a href="https://www.augustiniana.be/" target="_blank">Augustijns Historisch Instituut</a></p><p>Pakenstraat 65<br />
                    3001 Heverlee<br />
                    Belgium</p></font></div>',
                    'name' => 'Augustijns Historisch Instituut'
                ],
                [   'lon' => '6.075049269064763',
                    'lat' => '50.76167534226919',
                    'group' => 'bibliotheken',
                    'popup' => '<div><font color="black"><p><a href="https://mikado-ac.info/" target="_blank">Bibliothek des Internationalen Katholischen Missionswerkes missio e.V.</a></p><p>Goethestraße 43<br />
                    52064 Aachen</p></font></div>',
                    'name' => 'Bibliothek des Internationalen Katholischen Missionswerkes missio e.V.'
                ],
                [   'lon' => '6.951674384417203',
                    'lat' => '50.94479261008212',
                    'group' => 'bibliotheken',
                    'popup' => '<div><font color="black"><p><a href="https://www.dombibliothek-koeln.de/" target="_blank">Erzbischöfliche Diözesan- und Dombibliothek Köln mit Bibliothek St. Albertus Magnus</a></p><p>Kardinal-Frings-Straße 1-3<br />
                    50668 Köln</p></font></div>',
                    'name' => 'Erzbischöfliche Diözesan- und Dombibliothek Köln mit Bibliothek St. Albertus Magnus'
                ],
                [   'lon' => '7.622197697955276',
                    'lat' => '51.964666693546256',
                    'group' => 'bibliotheken',
                    'popup' => '<div><font color="black"><p><a href="https://www.dioezesanbibliothek-muenster.de/" target="_blank">Diözesanbibliothek Münster</a></p><p>Überwasserkirchplatz 2<br />
                    48143 Münster</p></font></div>',
                    'name' => 'Diözesanbibliothek Münster'
                ],
                [   'lon' => '9.141429953630837',
                    'lat' => '48.729964254476116',
                    'group' => 'bibliotheken',
                    'popup' => '<div><font color="black"><p><a href="https://www.ehz-bibliothek.elk-wue.de/" target="_blank">Evangelische Hochschul- und Zentralbibliothek Württemberg</a></p><p>Balinger Str. 33/1<br />
                    70567 Stuttgart</p></font></div>',
                    'name' => 'Evangelische Hochschul- und Zentralbibliothek Württemberg'
                ],
                [   'lon' => '10.70555979976137',
                    'lat' => '50.94367079300774',
                    'group' => 'bibliotheken',
                    'popup' => '<div><font color="black"><p><a href="https://www.uni-erfurt.de/forschungsbibliothek-gotha" target="_blank">Forschungsbibliothek Gotha </a></p><p>Schlossplatz 1<br />
                    99867 Gotha</p></font></div>',
                    'name' => 'Forschungsbibliothek Gotha'
                ],
                [   'lon' => '11.970895996080776',
                    'lat' => '51.47806422090299',
                    'group' => 'bibliotheken',
                    'popup' => '<div><font color="black"><p><a href="https://www.francke-halle.de/de/studienzentrum" target="_blank">Franckesche Stiftungen zu Halle </a></p><p>Franckeplatz 1<br />
                    06110 Halle</p></font></div>',
                    'name' => 'Franckesche Stiftungen zu Halle'
                ],
                [   'lon' => '9.983290211520224',
                    'lat' => '53.548993713747855',
                    'group' => 'bibliotheken',
                    'popup' => '<div><font color="black"><p><a href="https://ithf.de/" target="_blank">Institut für Theologie und Frieden (ithf)</a></p><p>Herrengraben 4<br />
                    20459 Hamburg</p></font></div>',
                    'name' => 'Institut für Theologie und Frieden (ithf)'
                ],
                [   'lon' => '7.201928284527916',
                    'lat' => '53.36566432753402',
                    'group' => 'bibliotheken',
                    'popup' => '<div><font color="black"><p><a href="https://www.jalb.de/" target="_blank">Johannes a Lasco Bibliothek</a></p><p>Kirchstraße 22<br />
                    26721 Emden</p></font></div>',
                    'name' => 'Johannes a Lasco Bibliothek'
                ],
                [   'lon' => '4.702528699758339',
                    'lat' => '50.87632481841027',
                    'group' => 'bibliotheken',
                    'popup' => '<div><font color="black"><p><a href="https://bib.kuleuven.be/" target="_blank">Maurits Sabbebibliotheek</a></p><p>Charles Deberiotstraat 26<br />
                    3000 Leuven<br />
                    Belgium</p></font></div>',
                    'name' => 'Maurits Sabbebibliotheek'
                ],
                [   'lon' => '-74.6663950736932',
                    'lat' => '40.34602383399463',
                    'group' => 'bibliotheken',
                    'popup' => '<div><font color="black"><p><a href="https://commons.ptsem.edu/" target="_blank">Princeton Theological Seminary</a></p><p>Wright Library<br />
                    25 Library Place<br />
                    Princeton, NJ 08540<br />
                    USA</p></font></div>',
                    'name' => 'Princeton Theological Seminary'
                ],
                [   'lon' => '12.63775312678687',
                    'lat' => '51.86630604784259',
                    'group' => 'bibliotheken',
                    'popup' => '<div><font color="black"><p><a href="https://www.rfb-wittenberg.de/" target="_blank">Reformationsgeschichtliche Forschungsbibliothek Wittenberg</a></p><p>Schlossplatz 1<br />
                    06886 Lutherstadt Wittenberg</p></font></div>',
                    'name' => 'Reformationsgeschichtliche Forschungsbibliothek Wittenberg'
                ],
                [   'lon' => '9.931365982513663',
                    'lat' => '49.79650515845315',
                    'group' => 'bibliotheken',
                    'popup' => '<div><font color="black"><p><a href="https://www.augustinus.de/" target="_blank">Zentrum für Augustinus-Forschung (ZAF)</a></p><p>Dominikanerplatz 4<br />
                    97070 Würzburg</p></font></div>',
                    'name' => 'Zentrum für Augustinus-Forschung (ZAF)'
                ],
                //ver
                [   'lon' => '8.755412855615948',
                    'lat' => '51.71397921606403',
                    'group' => 'ver',
                    'popup' => '<div><font color="black"><p><a href="https://www.akthb.de/" target="_blank">AKThB (Arbeitsgemeinschaft Katholisch-Theologischer Bibliotheken)</a></p><p>c/o Erzbischöfliche Akademische Bibliothek Paderborn<br />
                    Leostraße 21<br />
                    33098 Paderborn</p></font></div>',
                    'name' => 'AKThB (Arbeitsgemeinschaft Katholisch-Theologischer Bibliotheken)'
                ],
                [   'lon' => '5.890728113326436',
                    'lat' => '52.55379857836274',
                    'group' => 'ver',
                    'popup' => '<div><font color="black"><p><a href="https://beth.eu/" target="_blank">BETH (Bibliothèques Européennes de Théologie)</a></p><p>Waterkers 1<br />
                    8265 JJ Kampen<br />
                    Netherlands</p></font></div>',
                    'name' => 'BETH (Bibliothèques Européennes de Théologie)'
                ],
                [   'lon' => '8.532867524941635',
                    'lat' => '52.02174711455926',
                    'group' => 'ver',
                    'popup' => '<div><font color="black"><p><a href="https://vkwb.info/" target="_blank">VkwB (Verband kirchlich-wissenschaftlicher Bibliotheken)</a></p><p>c/o Evangelische Kirche von Westfalen<br />
                    Bibliothek des Landeskirchenamtes<br />
                    Altstädter Kirchplatz 5<br />
                    33602 Bielefeld</p></font></div>',
                    'name' => 'VkwB (Verband kirchlich-wissenschaftlicher Bibliotheken)'
                ],
                //fachin
                [   'lon' => '13.28832141332175',
                    'lat' => '52.452024786987835',
                    'group' => 'fachin',
                    'popup' => '<div><font color="black"><p><a href="https://www.propylaeum.de/" target="_blank">FID Altertumswissenschaften</a></p><p>Philologische Bibliothek der Freien Universität Berlin<br />
                    Habelschwerdter Allee 45<br />
                    14195 Berlin</p></font></div>',
                    'name' => 'FID Altertumswissenschaften'
                ],
                [   'lon' => '11.580364613130008',
                    'lat' => '48.147593189363796',
                    'group' => 'fachin',
                    'popup' => '<div><font color="black"><p><a href="https://www.historicum.net/home" target="_blank">FID Geschichtswissenschaft</a></p><p>Bayerische Staatsbibliothek<br />
                    Ludwigstr. 16<br />
                    80539 München</p></font></div><div><font color="black"><p><a href="https://www.osmikon.de/" target="_blank">FID Ost-, Ostmittel-, Südosteuropa (osmikon)</a></p><p>Bayerische Staatsbibliothek<br />
                    Ludwigstr. 16<br />
                    80539 München</p></font></div>',
                    'name' => 'FID Geschichtswissenschaft - FID Ost-, Ostmittel-, Südosteuropa (osmikon)'
                ],
                [   'lon' => '8.653098749724524',
                    'lat' => '50.12064052585044',
                    'group' => 'fachin',
                    'popup' => '<div><font color="black"><p><a href="https://portal.jewishstudies.de/" target="_blank">FID Jüdische Studien</a></p><p>Universitätsbibliothek Frankfurt am Main<br />
                    Freimannplatz 1<br />
                    60325 Frankfurt am Main</p></font></div>',
                    'name' => 'FID Jüdische Studien'
                ],
                [   'lon' => '8.70593708434894',
                    'lat' => '49.40981580401877',
                    'group' => 'fachin',
                    'popup' => '<div><font color="black"><p><a href="https://www.arthistoricum.net/" target="_blank">FID Kunst, Fotografie, Design</a></p><p>Universitätsbibliothek Heidelberg<br />
                    Plöck 107-109<br />
                    69117 Heidelberg</p></font></div>',
                    'name' => 'FID Kunst, Fotografie, Design'
                ],
                [   'lon' => '11.963601655605949',
                    'lat' => '51.49396906564426',
                    'group' => 'fachin',
                    'popup' => '<div><font color="black"><p><a href="https://www.menalib.de/" target="_blank">FID Nahost-, Nordafrika- und Islamstudien</a></p><p>Universitäts- und Landesbibliothek Sachsen-Anhalt<br />
                    Zweigbibliothek Vorderer Orient / Ethnologie<br />
                    Mühlweg 15<br />
                    06114 Halle (Saale)</p></font></div>',
                    'name' => 'FID Nahost-, Nordafrika- und Islamstudien'
                ],
                [   'lon' => '6.928387325417541',
                    'lat' => '50.92607082896188',
                    'group' => 'fachin',
                    'popup' => '<div><font color="black"><p><a href="https://philportal.de/" target="_blank">FID Philosophie</a></p><p>Universitäts- und Stadtbibliothek Köln<br />
                    Universitätsstraße 33<br />
                    50931 Köln</p></font></div>',
                    'name' => 'FID Philosophie'
                ],
                [   'lon' => '9.061992455474284',
                    'lat' => '48.52540627744962',
                    'group' => 'fachin',
                    'popup' => '<div><font color="black"><p><a href="https://relbib.de/" target="_blank">FID Religionswissenschaft</a></p><p>Universitätsbibliothek Tübingen<br />
                    Wilhelmstr. 32<br />
                    72074 Tübingen</p></font></div>',
                    'name' => 'FID Religionswissenschaft'
                ],
                [   'lon' => '13.32153185565181',
                    'lat' => '52.4982173456587',
                    'group' => 'fachin',
                    'popup' => '<div><font color="black"><p><a href="https://ireon-portal.de/" target="_blank">Fachinformationsverbund Internationale Beziehungen und Länderkunde (IREON)</a></p><p>Stiftung Wissenschaft und Politik (SWP)<br />
                    Deutsches Institut für Internationale Politik und Sicherheit<br />
                    Ludwigkirchplatz 3-4<br />
                    10719 Berlin</p></font></div>',
                    'name' => 'Fachinformationsverbund Internationale Beziehungen und Länderkunde (IREON)'
                ]
            ];

            if ($group !== 'all') {
                $partnersArray = array_values(array_filter($partnersArray, function ($item) use ($group) {
                    return $item['group'] === $group;
                }));
            }

            return $this->formatResponse(
                $partnersArray
            );

        }
    }
}
