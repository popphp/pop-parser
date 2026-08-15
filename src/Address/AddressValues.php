<?php
declare(strict_types=1);
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Parser\Address;

/**
 * Address values class
 *
 * @author     Mark Maher
 * @copyright  Copyright (c) 2021
 * @version    2.0.0
 */
class AddressValues
{
    /**
     * A list of common route type designators provided by USPS
     * @var array $commonRouteTypes
     * */
    protected static array $commonRouteTypes = [
        "avenue", "boulevard", "circle", "drive", "highway", "lane", "park", "park drive", "park dr", "parkway drive",
        "parkway dr", "parkway", "place", "place drive", "place dr", "road", "route", "square", "street", "terrace",
        "trace", "ave", "av", "blvd", "boul", "cir", "dr", "hwy", "ln ", "pk", "pky", "pl", "rd", "rte", "sq", "st",
        "terr", "trce",
    ];

    /**
     * A list of route type designators provided by USPS
     * @var array $routeTypes
     * */
    protected static array $routeTypes = [
        "allee" => "aly","alley" => "aly","ally" => "aly","aly" => "aly","anex" => "anx","annex" => "anx",
        "annx" => "anx","anx" => "anx","arc" => "arc","arcade" => "arc","bayoo" => "byu",
        "bayou" => "byu","bch" => "bch","beach" => "bch","bend" => "bnd","bg" => "bg","bgs" => "bgs","blf" => "blf",
        "blfs" => "blfs","bluf" => "blf","bluff" => "blf","bluffs" => "blfs","blvd" => "blvd","bnd" => "bnd",
        "bot" => "btm","bottm" => "btm","bottom" => "btm",
        "br" => "br","branch" => "br","brdge" => "brg","brg" => "brg","bridge" => "brg","brk" => "brk","brks" => "brks",
        "brnch" => "br","brook" => "brk","brooks" => "brks","btm" => "btm","burg" => "bg","burgs" => "bgs",
        "byp" => "byp","bypa" => "byp","bypas" => "byp","bypass" => "byp","byps" => "byp","byu" => "byu",
        "camp" => "cp","canyn" => "cyn","canyon" => "cyn","cape" => "cpe","causeway" => "cswy","causway" => "cswy",
        "cen" => "ctr","cent" => "ctr","center" => "ctr","centers" => "ctrs","centr" => "ctr","centre" => "ctr",
        "ck" => "crk","clb" => "clb","clf" => "clf","clfs" => "clfs","cliff" => "clf","cliffs" => "clfs",
        "club" => "clb","cmn" => "cmn","cmp" => "cp","cnter" => "ctr","cntr" => "ctr","cnyn" => "cyn",
        "common" => "cmn","cor" => "cor","corner" => "cor","corners" => "cors","cors" => "cors","course" => "crse",
        "court" => "ct","courts" => "cts","cove" => "cv","coves" => "cvs","cp" => "cp","cpe" => "cpe","cr" => "crk",
        "crcl" => "cir","crcle" => "cir","crecent" => "cres","creek" => "crk","cres" => "cres","crescent" => "cres",
        "cresent" => "cres","crest" => "crst","crk" => "crk","crossing" => "xing","crossroad" => "xrd",
        "crscnt" => "cres","crse" => "crse","crsent" => "cres","crsnt" => "cres","crssing" => "xing","crssng" => "xing",
        "crst" => "crst","crt" => "ct","cswy" => "cswy","ct" => "ct","ctr" => "ctr","ctrs" => "ctrs","cts" => "cts",
        "curv" => "curv","curve" => "curv","cv" => "cv","cvs" => "cvs","cyn" => "cyn","dale" => "dl","dam" => "dm",
        "div" => "dv","divide" => "dv","dl" => "dl","dm" => "dm","dv" => "dv","dvd" => "dv","est" => "est","estate" => "est",
        "estates" => "ests","ests" => "ests","exp" => "expy","expr" => "expy","express" => "expy","expressway" => "expy",
        "expw" => "expy","expy" => "expy","ext" => "ext","extension" => "ext","extensions" => "exts","extn" => "ext",
        "extnsn" => "ext","exts" => "exts","falls" => "fls","ferry" => "fry","field" => "fld","fields" => "flds",
        "flat" => "flt","flats" => "flts","fld" => "fld","flds" => "flds","fls" => "fls","flt" => "flt","flts" => "flts",
        "ford" => "frd","fords" => "frds","forest" => "frst","forests" => "frst","forg" => "frg","forge" => "frg",
        "forges" => "frgs","fork" => "frk","forks" => "frks","fort" => "ft","frd" => "frd","frds" => "frds",
        "freeway" => "fwy","freewy" => "fwy","frg" => "frg","frgs" => "frgs","frk" => "frk","frks" => "frks",
        "frry" => "fry","frst" => "frst","frt" => "ft","frway" => "fwy","frwy" => "fwy","fry" => "fry","ft" => "ft",
        "fwy" => "fwy","garden" => "gdn","gardens" => "gdns","gardn" => "gdn","gateway" => "gtwy","gatewy" => "gtwy",
        "gatway" => "gtwy","gdn" => "gdn","gdns" => "gdns","glen" => "gln","glens" => "glns","gln" => "gln",
        "glns" => "glns","grden" => "gdn","grdn" => "gdn","grdns" => "gdns","green" => "grn","greens" => "grns",
        "grn" => "grn","grns" => "grns","grov" => "grv","grove" => "grv","groves" => "grvs","grv" => "grv",
        "grvs" => "grvs","gtway" => "gtwy","gtwy" => "gtwy","harb" => "hbr","harbor" => "hbr","harbors" => "hbrs",
        "harbr" => "hbr","haven" => "hvn","havn" => "hvn","hbr" => "hbr","hbrs" => "hbrs","height" => "hts",
        "heights" => "hts","hgts" => "hts","hill" => "hl","hills" => "hls",
        "hiway" => "hwy","hiwy" => "hwy","hl" => "hl","hls" => "hls","hllw" => "holw","hollow" => "holw",
        "hollows" => "holw","holw" => "holw","holws" => "holw","hrbor" => "hbr","ht" => "hts","hts" => "hts",
        "hvn" => "hvn","hway" => "hwy","hwy" => "hwy","inlet" => "inlt","inlt" => "inlt","is" => "is","island" => "is",
        "islands" => "iss","isle" => "isle","isles" => "isle","islnd" => "is","islnds" => "iss","iss" => "iss",
        "jct" => "jct","jction" => "jct","jctn" => "jct","jctns" => "jcts","jcts" => "jcts","junction" => "jct",
        "junctions" => "jcts","junctn" => "jct","juncton" => "jct","key" => "ky","keys" => "kys","knl" => "knl",
        "knls" => "knls","knol" => "knl","knoll" => "knl","knolls" => "knls","ky" => "ky","kys" => "kys","la" => "ln",
        "lake" => "lk","lakes" => "lks","landing" => "lndg","lck" => "lck",
        "lcks" => "lcks","ldg" => "ldg","ldge" => "ldg","lf" => "lf","lgt" => "lgt","lgts" => "lgts","light" => "lgt",
        "lights" => "lgts","lk" => "lk","lks" => "lks","ln" => "ln","lndg" => "lndg","lndng" => "lndg","loaf" => "lf",
        "lock" => "lck","locks" => "lcks","lodg" => "ldg","lodge" => "ldg","loop" => "loop","loops" => "loop","manor" => "mnr",
        "manors" => "mnrs","mdw" => "mdw","mdws" => "mdws","meadow" => "mdw","meadows" => "mdws","medows" => "mdws",
        "mill" => "ml","mills" => "mls","mission" => "msn","missn" => "msn","ml" => "ml","mls" => "mls","mnr" => "mnr",
        "mnrs" => "mnrs","mnt" => "mt","mntain" => "mtn","mntn" => "mtn","mntns" => "mtns","motorway" => "mtwy",
        "mount" => "mt","mountain" => "mtn","mountains" => "mtns","mountin" => "mtn","msn" => "msn","mssn" => "msn",
        "mt" => "mt","mtin" => "mtn","mtn" => "mtn","mtns" => "mtns","mtwy" => "mtwy","nck" => "nck","neck" => "nck",
        "opas" => "opas","orch" => "orch","orchard" => "orch","orchrd" => "orch","oval" => "oval","overpass" => "opas",
        "ovl" => "oval",
        "pass" => "pass","passage" => "psge","path" => "path","paths" => "path","pike" => "pike","pikes" => "pike",
        "pine" => "pne","pines" => "pnes","pk" => "park","plain" => "pln","plaines" => "plns","plains" => "plns",
        "plaza" => "plz","pln" => "pln","plns" => "plns","plz" => "plz","plza" => "plz","pne" => "pne","pnes" => "pnes",
        "point" => "pt","points" => "pts","port" => "prt","ports" => "prts","pr" => "pr","prairie" => "pr",
        "prarie" => "pr","prk" => "park","prr" => "pr","prt" => "prt","prts" => "prts","psge" => "psge","pt" => "pt",
        "pts" => "pts","rad" => "radl","radial" => "radl","radiel" => "radl","radl" => "radl","ranch" => "rnch",
        "ranches" => "rnch","rapid" => "rpd","rapids" => "rpds","rd" => "rd","rdg" => "rdg","rdge" => "rdg",
        "rdgs" => "rdgs","rds" => "rds","rest" => "rst","ridge" => "rdg","ridges" => "rdgs","riv" => "riv",
        "river" => "riv","rivr" => "riv","rnch" => "rnch","rnchs" => "rnch","rpd" => "rpd","rpds" => "rpds",
        "rst" => "rst","rte" => "rte","rvr" => "riv",
        "row" => "row","shl" => "shl","shls" => "shls","shoal" => "shl","shoals" => "shls","shoar" => "shr",
        "shoars" => "shrs","shore" => "shr","shores" => "shrs","shr" => "shr","shrs" => "shrs","skwy" => "skwy",
        "skyway" => "skwy","smt" => "smt","spg" => "spg","spgs" => "spgs","spng" => "spg","spngs" => "spgs",
        "spring" => "spg","springs" => "spgs","sprng" => "spg","sprngs" => "spgs","spur" => "spur","spurs" => "spur",
        "sq" => "sq","sqr" => "sq","sqre" => "sq","sqrs" => "sqs","sqs" => "sqs","sta" => "sta","station" => "sta",
        "statn" => "sta","stn" => "sta",
        "str" => "st","stra" => "stra","strav" => "stra","strave" => "stra","straven" => "stra","stravenue" => "stra",
        "stravn" => "stra","stream" => "strm","street" => "st","streets" => "sts","streme" => "strm","strm" => "strm",
        "strt" => "st","strvn" => "stra","strvnue" => "stra","sts" => "sts","sumit" => "smt","sumitt" => "smt",
        "summit" => "smt","throughway" => "trwy","tpk" => "tpke",
        "tpke" => "tpke","tr" => "trl","track" => "trak","tracks" => "trak",
        "trafficway" => "trfy","trail" => "trl","trails" => "trl","trak" => "trak","trce" => "trce","trfy" => "trfy",
        "trk" => "trak","trks" => "trak","trl" => "trl","trls" => "trl","trnpk" => "tpke","trpk" => "tpke","trwy" => "trwy",
        "tunel" => "tunl","tunl" => "tunl","tunls" => "tunl","tunnel" => "tunl","tunnels" => "tunl","tunnl" => "tunl",
        "turnpike" => "tpke","turnpk" => "tpke","underpass" => "upas","un" => "un","union" => "un","unions" => "uns",
        "uns" => "uns","upas" => "upas","valley" => "vly","valleys" => "vlys","vally" => "vly","vdct" => "via","via" => "via",
        "viadct" => "via","viaduct" => "via","view" => "vw","views" => "vws","vill" => "vlg","villag" => "vlg",
        "village" => "vlg","villages" => "vlgs","ville" => "vl","villg" => "vlg","villiage" => "vlg","vis" => "vis",
        "vist" => "vis","vista" => "vis","vl" => "vl","vlg" => "vlg","vlgs" => "vlgs","vlly" => "vly","vly" => "vly",
        "vlys" => "vlys","vst" => "vis","vsta" => "vis","vw" => "vw","vws" => "vws","walk" => "walk","walks" => "walk",
        "way" => "way","well" => "wl","wells" => "wls","wl" => "wl","wls" => "wls","wy" => "way","xing" => "xing","xrd" => "xrd"
    ];

    /**
     * A list of possible directional indicators
     * @var array $directions
     * */
    protected static array $directions = [
        'WSW'   => 'West-Southwest',
        'ESE'   => 'East-Southeast',
        'SSW'   => 'South-Southwest',
        'SSE'   => 'South-Southeast',
        'WNW'   => 'West-Northwest',
        'ENE'   => 'East-Northeast',
        'NNW'   => 'North-Northwest',
        'NNE'   => 'North-Northeast',
        'SW'    => 'Southwest',
        'SE'    => 'Southeast',
        'NW'    => 'Northwest',
        'NE'    => 'Northeast',
        ' N '   => 'North',
        ' S '   => 'South',
        ' E '   => 'East',
        ' W '   => 'West',
        'N.'    => 'North',
        'S.'    => 'South',
        'E.'    => 'East',
        'W.'    => 'West',
    ];

    /**
     * A list of states and state codes
     * @var array $states
     * */
    protected static array $states = [
        'US' => [
            'AL' => 'Alabama',
            'AK' => 'Alaska',
            'AZ' => 'Arizona',
            'AR' => 'Arkansas',
            'CA' => 'California',
            'CO' => 'Colorado',
            'CT' => 'Connecticut',
            'DE' => 'Delaware',
            'DC' => 'District of Columbia',
            'FL' => 'Florida',
            'GA' => 'Georgia',
            'HI' => 'Hawaii',
            'ID' => 'Idaho',
            'IL' => 'Illinois',
            'IN' => 'Indiana',
            'IA' => 'Iowa',
            'KS' => 'Kansas',
            'KY' => 'Kentucky',
            'LA' => 'Louisiana',
            'ME' => 'Maine',
            'MD' => 'Maryland',
            'MA' => 'Massachusetts',
            'MI' => 'Michigan',
            'MN' => 'Minnesota',
            'MS' => 'Mississippi',
            'MO' => 'Missouri',
            'MT' => 'Montana',
            'NE' => 'Nebraska',
            'NV' => 'Nevada',
            'NH' => 'New Hampshire',
            'NJ' => 'New Jersey',
            'NM' => 'New Mexico',
            'NY' => 'New York',
            'NC' => 'North Carolina',
            'ND' => 'North Dakota',
            'OH' => 'Ohio',
            'OK' => 'Oklahoma',
            'OR' => 'Oregon',
            'PA' => 'Pennsylvania',
            'PR' => 'Puerto Rico',
            'RI' => 'Rhode Island',
            'SC' => 'South Carolina',
            'SD' => 'South Dakota',
            'TN' => 'Tennessee',
            'TX' => 'Texas',
            'UT' => 'Utah',
            'VT' => 'Vermont',
            'VA' => 'Virginia',
            'WA' => 'Washington',
            'WV' => 'West Virginia',
            'WI' => 'Wisconsin',
            'WY' => 'Wyoming',
        ],
        'CA' => [
            'AB' => 'Alberta',
            'BC' => 'British Columbia',
            'MB' => 'Manitoba',
            'NB' => 'New Brunswick',
            'NL' => 'Newfoundland and Labrador',
            'NT' => 'Northwest Territories',
            'NS' => 'Nova Scotia',
            'NU' => 'Nunavut',
            'ON' => 'Ontario',
            'PE' => 'Prince Edward Island',
            'QC' => 'Quebec',
            'SK' => 'Saskatchewan',
            'YT' => 'Yukon',
        ]
    ];

    /**
     * A list of possibly unit types
     * @var array $unitTypes
     * */
    protected static array $unitTypes = [
        'APARTMENT'  => 'APT',
        'APT'        => 'APT',
        'BASEMENT'   => 'BSMT',
        'BSMT'       => 'BSMT',
        'BLDG'       => 'BLDG',
        'BUILDING'   => 'BLDG',
        'DEPARTMENT' => 'DEPT',
        'DEPT'       => 'DEPT',
        'FL'         => 'FL',
        'FLR'        => 'FL',
        'FLOOR'      => 'FL',
        'FRNT'       => 'FRNT',
        'FRONT'      => 'FRNT',
        'HANGAR'     => 'HNGR',
        'HNGR'       => 'HNGR',
        'LBBY'       => 'LBBY',
        'LOBBY'      => 'LBBY',
        'LOT'        => 'LOT',
        'LOWER'      => 'LOWR',
        'LOWR'       => 'LOWER',
        'NO'         => 'NUMBER',
        'OFC'        => 'OFC',
        'OFFICE'     => 'OFC',
        'PENTHOUSE'  => 'PH',
        'PH'         => 'PH',
        'PIER'       => 'PIER',
        'REAR'       => 'REAR',
        'RM'         => 'RM',
        'ROOM'       => 'RM',
        'SIDE'       => 'SIDE',
        'SLIP'       => 'SLIP',
        'SPACE'      => 'SPC',
        'SPC'        => 'SPC',
        'STE'        => 'STE',
        'STOP'       => 'STOP',
        'SUITE'      => 'STE',
        'TRAILER'    => 'TRLR',
        'TRLR'       => 'TRLR',
        'UNIT'       => 'UNIT',
        'UPPER'      => 'UPPR',
        'UPPR'       => 'UPPR',
        '#'          => '#',
    ];


    /**
     * Getter method for accessing common route types list
     *
     * @return array
     */
    public function getCommonRouteTypes(): array
    {
        $routes = self::$commonRouteTypes;

        usort($routes, function($a, $b) {
            return strlen($b) - strlen($a);
        });

        return $routes;
    }

    /**
     * Getter method for accessing route types list
     *
     * @param  bool $merge
     * @return array
     */
    public function getRouteTypes(bool $merge = false): array
    {
        $routes = self::$routeTypes;

        if ($merge) {
            $keys   = array_keys($routes);
            $values = array_values($routes);
            $routes = array_unique(array_merge($keys, $values));

            usort($routes, function($a, $b) {
                return strlen($b) - strlen($a);
            });
        }

        return $routes;
    }

    /**
     * Getter method for accessing directionals associative array
     *
     * @return array
     */
    public function getDirections(): array
    {
        $directions = array_unique(array_merge(array_values(self::$directions), array_keys(self::$directions)));
        usort($directions, function ($a, $b) {return strlen($b) - strlen($a);});
        return $directions;
    }

    /**
     * Getter method for accessing states associative array
     *
     * @param  string $country
     * @return array
     */
    public function getStates(string $country = 'US'): array
    {
        return self::$states[$country];
    }

    /**
     * Getter method for accessing two-letter state abbreviations
     *
     * @param  string $country
     * @return array
     */
    public function getStateCodes(string $country = 'US'): array
    {
        return array_keys(self::$states[$country]);
    }

    /**
     * Getter method for accessing full state names
     *
     * @param  string $country
     * @return array
     */
    public function getStateNames(string $country = 'US'): array
    {
        return array_values(self::$states[$country]);
    }

    /**
     * Getter method for accessing unit types list
     *
     * @return array
     */
    public function getUnitTypes(): array
    {
        $unitTypes = array_merge(array_values(self::$unitTypes), array_keys(self::$unitTypes));
        usort($unitTypes, function ($a, $b) {return strlen($b) - strlen($a);});
        return array_unique($unitTypes);
    }

}
