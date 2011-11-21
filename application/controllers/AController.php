<?php
/**
 * Annual Checklist Interface
 *
 * Class AController
 * Abstract controller class
 *
 * @category    ACI
 * @package     application
 * @subpackage  controllers
 *
 */
abstract class AController extends Zend_Controller_Action
{
    protected $_logger;
    protected $_db;
    // Advanced options from ini available as properties
    protected $_cookieExpiration;
    protected $_webserviceTimeout;

    public function init ()
    {
        $this->_logger = Zend_Registry::get('logger');
        // Convert POST to GET request as friendly URL
        $this->_postToGet();
        $this->_db = Zend_Registry::get('db');
        $this->_logger->debug($this->_getAllParams());
        // Add custom view helpers path
        $this->view->addHelperPath(
            APPLICATION_PATH . '/views/helpers/', 'ACI_View_Helper_');
        // Initialize Dojo, disabled by default
        Zend_Dojo::enableView($this->view);
        $this->view->dojo()->disable();
        $this->view->controller = $this->getRequest()->controller;
        $this->view->action = $this->getRequest()->action;
        $this->view->latestSearch = $this->getHelper('Query')->getLatestQuery();
        $config = Zend_Registry::get('config');
        $this->view->app = $config->eti->application;
        $this->view->googleAnalyticsTrackerId = $config->view->googleAnalytics->trackerId;
        $this->view->interfaceLanguages = $this->_setInterfaceLanguages();
        $this->view->isocode2Toisocode3 = array("aa" => "aar",
			"ab" => "abk",
			"" => "ace",
			"" => "ach",
			"" => "ada",
			"" => "ady",
			"" => "afa",
			"" => "afh",
			"af" => "afr",
			"" => "ain",
			"ak" => "aka",
			"" => "akk",
			"sq" => "alb (B) sqi (T)",
			"" => "ale",
			"" => "alg",
			"" => "alt",
			"am" => "amh",
			"" => "ang",
			"" => "anp",
			"" => "apa",
			"ar" => "ara",
			"" => "arc",
			"an" => "arg",
			"hy" => "arm (B) hye (T)",
			"" => "arn",
			"" => "arp",
			"" => "art",
			"" => "arw",
			"as" => "asm",
			"" => "ast",
			"" => "ath",
			"" => "aus",
			"av" => "ava",
			"ae" => "ave",
			"" => "awa",
			"ay" => "aym",
			"az" => "aze",
			"" => "bad",
			"" => "bai",
			"ba" => "bak",
			"" => "bal",
			"bm" => "bam",
			"" => "ban",
			"eu" => "baq (B) eus (T)",
			"" => "bas",
			"" => "bat",
			"" => "bej",
			"be" => "bel",
			"" => "bem",
			"bn" => "ben",
			"" => "ber",
			"" => "bho",
			"bh" => "bih",
			"" => "bik",
			"" => "bin",
			"bi" => "bis",
			"" => "bla",
			"" => "bnt",
			"bo" => "tib (B) bod (T)",
			"bs" => "bos",
			"" => "bra",
			"br" => "bre",
			"" => "btk",
			"" => "bua",
			"" => "bug",
			"bg" => "bul",
			"my" => "bur (B) mya (T)",
			"" => "byn",
			"" => "cad",
			"" => "cai",
			"" => "car",
			"ca" => "cat",
			"" => "cau",
			"" => "ceb",
			"" => "cel",
			"cs" => "cze (B) ces (T)",
			"ch" => "cha",
			"" => "chb",
			"ce" => "che",
			"" => "chg",
			"zh" => "chi (B) zho (T)",
			"" => "chk",
			"" => "chm",
			"" => "chn",
			"" => "cho",
			"" => "chp",
			"" => "chr",
			"cu" => "chu",
			"cv" => "chv",
			"" => "chy",
			"" => "cmc",
			"" => "cop",
			"kw" => "cor",
			"co" => "cos",
			"" => "cpe",
			"" => "cpf",
			"" => "cpp",
			"cr" => "cre",
			"" => "crh",
			"" => "crp",
			"" => "csb",
			"" => "cus",
			"cy" => "wel (B) cym (T)",
			"cs" => "cze (B) ces (T)",
			"" => "dak",
			"da" => "dan",
			"" => "dar",
			"" => "day",
			"" => "del",
			"" => "den",
			"de" => "ger (B) deu (T)",
			"" => "dgr",
			"" => "din",
			"dv" => "div",
			"" => "doi",
			"" => "dra",
			"" => "dsb",
			"" => "dua",
			"" => "dum",
			"nl" => "dut (B) nld (T)",
			"" => "dyu",
			"dz" => "dzo",
			"" => "efi",
			"" => "egy",
			"" => "eka",
			"el" => "gre (B) ell (T)",
			"" => "elx",
			"en" => "eng",
			"" => "enm",
			"eo" => "epo",
			"et" => "est",
			"eu" => "baq (B) eus (T)",
			"ee" => "ewe",
			"" => "ewo",
			"" => "fan",
			"fo" => "fao",
			"fa" => "per (B) fas (T)",
			"" => "fat",
			"fj" => "fij",
			"" => "fil",
			"fi" => "fin",
			"" => "fiu",
			"" => "fon",
			"fr" => "fre (B) fra (T)",
			"fr" => "fre (B) fra (T)",
			"" => "frm",
			"" => "fro",
			"" => "frr",
			"" => "frs",
			"fy" => "fry",
			"ff" => "ful",
			"" => "fur",
			"" => "gaa",
			"" => "gay",
			"" => "gba",
			"" => "gem",
			"ka" => "geo (B) kat (T)",
			"de" => "ger (B) deu (T)",
			"" => "gez",
			"" => "gil",
			"gd" => "gla",
			"ga" => "gle",
			"gl" => "glg",
			"gv" => "glv",
			"" => "gmh",
			"" => "goh",
			"" => "gon",
			"" => "gor",
			"" => "got",
			"" => "grb",
			"" => "grc",
			"el" => "gre (B) ell (T)",
			"gn" => "grn",
			"" => "gsw",
			"gu" => "guj",
			"" => "gwi",
			"" => "hai",
			"ht" => "hat",
			"ha" => "hau",
			"" => "haw",
			"he" => "heb",
			"hz" => "her",
			"" => "hil",
			"" => "him",
			"hi" => "hin",
			"" => "hit",
			"" => "hmn",
			"ho" => "hmo",
			"hr" => "hrv",
			"" => "hsb",
			"hu" => "hun",
			"" => "hup",
			"hy" => "arm (B) hye (T)",
			"" => "iba",
			"ig" => "ibo",
			"is" => "ice (B) isl (T)",
			"io" => "ido",
			"ii" => "iii",
			"" => "ijo",
			"iu" => "iku",
			"ie" => "ile",
			"" => "ilo",
			"ia" => "ina",
			"" => "inc",
			"id" => "ind",
			"" => "ine",
			"" => "inh",
			"ik" => "ipk",
			"" => "ira",
			"" => "iro",
			"is" => "ice (B) isl (T)",
			"it" => "ita",
			"jv" => "jav",
			"" => "jbo",
			"ja" => "jpn",
			"" => "jpr",
			"" => "jrb",
			"" => "kaa",
			"" => "kab",
			"" => "kac",
			"kl" => "kal",
			"" => "kam",
			"kn" => "kan",
			"" => "kar",
			"ks" => "kas",
			"ka" => "geo (B) kat (T)",
			"kr" => "kau",
			"" => "kaw",
			"kk" => "kaz",
			"" => "kbd",
			"" => "kha",
			"" => "khi",
			"km" => "khm",
			"" => "kho",
			"ki" => "kik",
			"rw" => "kin",
			"ky" => "kir",
			"" => "kmb",
			"" => "kok",
			"kv" => "kom",
			"kg" => "kon",
			"ko" => "kor",
			"" => "kos",
			"" => "kpe",
			"" => "krc",
			"" => "krl",
			"" => "kro",
			"" => "kru",
			"kj" => "kua",
			"" => "kum",
			"ku" => "kur",
			"" => "kut",
			"" => "lad",
			"" => "lah",
			"" => "lam",
			"lo" => "lao",
			"la" => "lat",
			"lv" => "lav",
			"" => "lez",
			"li" => "lim",
			"ln" => "lin",
			"lt" => "lit",
			"" => "lol",
			"" => "loz",
			"lb" => "ltz",
			"" => "lua",
			"lu" => "lub",
			"lg" => "lug",
			"" => "lui",
			"" => "lun",
			"" => "luo",
			"" => "lus",
			"mk" => "mac (B) mkd (T)",
			"" => "mad",
			"" => "mag",
			"mh" => "mah",
			"" => "mai",
			"" => "mak",
			"ml" => "mal",
			"" => "man",
			"mi" => "mao (B) mri (T)",
			"" => "map",
			"mr" => "mar",
			"" => "mas",
			"ms" => "may (B) msa (T)",
			"" => "mdf",
			"" => "mdr",
			"" => "men",
			"" => "mga",
			"" => "mic",
			"" => "min",
			"" => "mis",
			"mk" => "mac (B) mkd (T)",
			"" => "mkh",
			"mg" => "mlg",
			"mt" => "mlt",
			"" => "mnc",
			"" => "mni",
			"" => "mno",
			"" => "moh",
			"mn" => "mon",
			"" => "mos",
			"mi" => "mao (B) mri (T)",
			"ms" => "may (B) msa (T)",
			"" => "mul",
			"" => "mun",
			"" => "mus",
			"" => "mwl",
			"" => "mwr",
			"my" => "bur (B) mya (T)",
			"" => "myn",
			"" => "myv",
			"" => "nah",
			"" => "nai",
			"" => "nap",
			"na" => "nau",
			"nv" => "nav",
			"nr" => "nbl",
			"nd" => "nde",
			"ng" => "ndo",
			"" => "nds",
			"ne" => "nep",
			"" => "new",
			"" => "nia",
			"" => "nic",
			"" => "niu",
			"nl" => "dut (B) nld (T)",
			"nn" => "nno",
			"nb" => "nob",
			"" => "nog",
			"" => "non",
			"no" => "nor",
			"" => "nqo",
			"" => "nso",
			"" => "nub",
			"" => "nwc",
			"ny" => "nya",
			"" => "nym",
			"" => "nyn",
			"" => "nyo",
			"" => "nzi",
			"oc" => "oci",
			"oj" => "oji",
			"or" => "ori",
			"om" => "orm",
			"" => "osa",
			"os" => "oss",
			"" => "ota",
			"" => "oto",
			"" => "paa",
			"" => "pag",
			"" => "pal",
			"" => "pam",
			"pa" => "pan",
			"" => "pap",
			"" => "pau",
			"" => "peo",
			"fa" => "per (B) fas (T)",
			"" => "phi",
			"" => "phn",
			"pi" => "pli",
			"pl" => "pol",
			"" => "pon",
			"pt" => "por",
			"" => "pra",
			"" => "pro",
			"ps" => "pus",
			"" => "qaa-qtz",
			"qu" => "que",
			"" => "raj",
			"" => "rap",
			"" => "rar",
			"" => "roa",
			"rm" => "roh",
			"" => "rom",
			"ro" => "rum (B) ron (T)",
			"ro" => "rum (B) ron (T)",
			"rn" => "run",
			"" => "rup",
			"ru" => "rus",
			"" => "sad",
			"sg" => "sag",
			"" => "sah",
			"" => "sai",
			"" => "sal",
			"" => "sam",
			"sa" => "san",
			"" => "sas",
			"" => "sat",
			"" => "scn",
			"" => "sco",
			"" => "sel",
			"" => "sem",
			"" => "sga",
			"" => "sgn",
			"" => "shn",
			"" => "sid",
			"si" => "sin",
			"" => "sio",
			"" => "sit",
			"" => "sla",
			"sk" => "slo (B) slk (T)",
			"sk" => "slo (B) slk (T)",
			"sl" => "slv",
			"" => "sma",
			"se" => "sme",
			"" => "smi",
			"" => "smj",
			"" => "smn",
			"sm" => "smo",
			"" => "sms",
			"sn" => "sna",
			"sd" => "snd",
			"" => "snk",
			"" => "sog",
			"so" => "som",
			"" => "son",
			"st" => "sot",
			"es" => "spa",
			"sq" => "alb (B) sqi (T)",
			"sc" => "srd",
			"" => "srn",
			"sr" => "srp",
			"" => "srr",
			"" => "ssa",
			"ss" => "ssw",
			"" => "suk",
			"su" => "sun",
			"" => "sus",
			"" => "sux",
			"sw" => "swa",
			"sv" => "swe",
			"" => "syc",
			"" => "syr",
			"ty" => "tah",
			"" => "tai",
			"ta" => "tam",
			"tt" => "tat",
			"te" => "tel",
			"" => "tem",
			"" => "ter",
			"" => "tet",
			"tg" => "tgk",
			"tl" => "tgl",
			"th" => "tha",
			"bo" => "tib (B) bod (T)",
			"" => "tig",
			"ti" => "tir",
			"" => "tiv",
			"" => "tkl",
			"" => "tlh",
			"" => "tli",
			"" => "tmh",
			"" => "tog",
			"to" => "ton",
			"" => "tpi",
			"" => "tsi",
			"tn" => "tsn",
			"ts" => "tso",
			"tk" => "tuk",
			"" => "tum",
			"" => "tup",
			"tr" => "tur",
			"" => "tut",
			"" => "tvl",
			"tw" => "twi",
			"" => "tyv",
			"" => "udm",
			"" => "uga",
			"ug" => "uig",
			"uk" => "ukr",
			"" => "umb",
			"" => "und",
			"ur" => "urd",
			"uz" => "uzb",
			"" => "vai",
			"ve" => "ven",
			"vi" => "vie",
			"vo" => "vol",
			"" => "vot",
			"" => "wak",
			"" => "wal",
			"" => "war",
			"" => "was",
			"cy" => "wel (B) cym (T)",
			"" => "wen",
			"wa" => "wln",
			"wo" => "wol",
			"" => "xal",
			"xh" => "xho",
			"" => "yao",
			"" => "yap",
			"yi" => "yid",
			"yo" => "yor",
			"" => "ypk",
			"" => "zap",
			"" => "zbl",
			"" => "zen",
			"za" => "zha",
			"zh" => "chi (B) zho (T)",
			"" => "znd",
			"zu" => "zul",
			"" => "zun",
			"" => "zxx",
			"" => "zza",
        	"pt_BR" => "por(BR)"
        );
        $this->_cookieExpiration = $this->_setCookieExpiration();
        $this->view->cookieExpiration = $this->_cookieExpiration;
        $this->_webserviceTimeout = $this->_setWebserviceTimeout();
        $this->view->statisticsModuleEnabled = $this->_moduleEnabled('statistics');
    }

    public function getDbAdapter ()
    {
        return $this->_db;
    }

    protected function _getSearchForm ()
    {
        return $this->getHelper('FormLoader')->getSearchForm();
    }

    protected function _renderFormPage ($header, $form)
    {
        if ($form instanceof ACI_Form_Dojo_AMultiCombo) {
            $this->view->dojo()->registerModulePath('ACI', 
                $this->view->baseUrl() . JS_PATH . '/library/ACI')->requireModule(
                'ACI.dojo.TxReadStore');
            // ComboBox (v1.3.2) custom extension
            $this->view->headScript()->appendFile(
                $this->view->baseUrl() . JS_PATH . '/ComboBox.ext.js');
        }
        $this->getHelper('Renderer')->renderFormPage($header, $form);
    }

    protected function _renderResultsPage (array $elements = array())
    {
        $this->getHelper('Renderer')->renderResultsPage($elements);
    }

    protected function _exportResults ()
    {
        $this->view->layout()->disableLayout();
        $fileName = 'CoL_data.csv';
        $controller = $this->getHelper('Query')->getLatestQueryController();
        $action = $this->getHelper('Query')->getLatestQueryAction();
        $latestSelect = $this->getHelper('Query')->getLatestSelect();
        if (!$latestSelect instanceof Zend_Db_Select) {
            $this->getHelper('Export')->setHeaders($fileName);
            exit('');
        }
        $this->getHelper('Export')->csv($controller, $action, $latestSelect, $fileName);
    }

    protected function _setSessionFromParams (array $values)
    {
        foreach ($values as $v) {
            $this->getHelper('SessionHandler')->set($v, $this->_getParam($v));
        }
    }

    protected function _setParamsFromSession (array $params)
    {
        foreach ($params as $p) {
            $v = $this->getHelper('SessionHandler')->get($p);
            if ($v !== null) {
                $this->_logger->debug("Setting $p to $v from session");
                $this->_setParam($p, $v);
            }
        }
    }
    
    protected function _createJsTranslationArray (array $jsTranslation)
    {
        $translator = Zend_Registry::get('Zend_Translate');
        $jsArray = "var translations = new Array();\n";
        foreach ($jsTranslation as $v) {
            $jsArray .= "\ttranslations['$v'] = '".$translator->translate($v)."';\n";
        }
        return $jsArray;
    }

    /**
     * Redirects a request using the given action, controller, module and params
     * in the request
     */
    protected function _postToGet ()
    {
        if ($this->getRequest()->isPost()) {
            $params = array();
            // Remove unneeded parameters
            $exclude = array(
                'action', 
                'controller', 
                'module', 
                'search', 
                'update', 
                'clear'
            );
            foreach ($this->getRequest()->getParams() as $k => $v) {
                if (!in_array($k, $exclude) && strlen(trim($v))) {
                    $params[$k] = $v;
                }
            }
            $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
            $redirector->setGotoSimple($this->getRequest()->getParam('action'), 
                $this->getRequest()->getParam('controller'), 
                $this->getRequest()->getParam('module'), $params);
        }
    }

    protected function _moduleEnabled ($module)
    {
        return Bootstrap::instance()->getOption('module.' . $module);
    }

    private function _setInterfaceLanguages ()
    {
    	/*
    	 * Protecting the current way the languages are made in the menu.
    	 */
/*        $locale = new Zend_Locale($this->view->language);
        $allLanguages = Bootstrap::instance()->getOption('language');
        $selectedLanguages = array_flip(array_keys($allLanguages, 1));
        $languageScripts = $locale->getTranslationList('ScriptToLanguage', 'en');
        // Strip off the potentially present locale first when checking for scripts!
        $currentLanguageScripts = explode(' ', $languageScripts[substr($this->view->language, 0, 2)]);
        foreach ($selectedLanguages as $iso => $language) {
            $selectedLanguages[$iso] = ucfirst(
                $locale->getTranslation($iso, 'language', $iso));
            // Append transliteration script(s) of this language does not match script(s) of current language
            // Strip off the potentially present locale first when checking for scripts!
            $scripts = explode(' ', $languageScripts[substr($iso, 0, 2)]);
            if (count(array_intersect($currentLanguageScripts, $scripts)) == 0) {
                $selectedLanguages[$iso] .= ' (' . ucfirst(
                    $locale->getTranslation($iso, 'language', 
                        $this->view->language)) . ')';
            }
        }
        asort($selectedLanguages, SORT_LOCALE_STRING);
        return $selectedLanguages;*/
    	$locale = new Zend_Locale($this->view->language);
        $allLanguages = Bootstrap::instance()->getOption('language');
        $selectedLanguages = array_flip(array_keys($allLanguages, 1));
        $languageScripts = $locale->getTranslationList('ScriptToLanguage', 'en');
        // Strip off the potentially present locale first when checking for scripts!
        $currentLanguageScripts = explode(' ', $languageScripts[substr($this->view->language, 0, 2)]);
        foreach ($selectedLanguages as $iso => $language) {
            $selectedLanguages[$iso] = ucfirst(
                $locale->getTranslation($iso, 'language', $iso));
            // Append transliteration script(s) of this language does not match script(s) of current language
            // Strip off the potentially present locale first when checking for scripts!
            $scripts = explode(' ', $languageScripts[substr($iso, 0, 2)]);
            if ($iso != 'en') {
                $selectedLanguages[$iso] .= ' (' . ucfirst(
                    $locale->getTranslation($iso, 'language', 
                        $this->view->language)) . ')';
            }
        }
        asort($selectedLanguages, SORT_LOCALE_STRING);
        return $selectedLanguages;
    }

    private function _setCookieExpiration ()
    {
        $config = Zend_Registry::get('config');
        return $config->advanced->cookie_expiration;
    }

    private function _setWebserviceTimeout ()
    {
        $config = Zend_Registry::get('config');
        $timeout = $config->advanced->webservice_timeout;
        // Maximize to 10s
        if (empty($timeout) || $timeout > 10) {
            $timeout = 10;
        }
        return $timeout;
    }
}