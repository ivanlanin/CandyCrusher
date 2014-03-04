<?php
/**
 * CandyCrusher.php
 * @since   2014-03-04
 */

/**
 * CandyCrusher class
 *
 * @package CandyCrusher
 * @require PHP >= 5.2.0
 */
class CandyCrusher
{

    /**
     * Session ID
     * @var string
     */
    var $session;

    /**
     * User ID
     * @var string
     */
    var $user;

    /**
     * Dreamworld
     * @var bool
     */
    var $dreamworld = false;

    /**
     * Raw game data
     * @var array
     */
    var $gameData;

    /**
     * Level definitions
     * @var array
     */
    var $levels;

    /**
     * Constructor
     *
     * @param   string  $session Session ID
     * @param   bool    $dreamword Reality or Dreamworld?
     */
    function __construct($session, $dreamworld = false)
    {
        $this->session = $session;
        $this->dreamworld = $dreamworld;
        $this->initGame();
    }

    /**
     * Get API
     *
     * @param   string  $method Method name
     * @param   array   $param Parameters
     * @return  string  JSON response
     */
    function callApi($method, $param)
    {
        $agent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; en; rv:1.9.0.4) Gecko/2009011913 Firefox/3.0.6";
        $baseUrl = 'http://candycrush.king.com/api';
        $query = http_build_query($param);
        $url = "{$baseUrl}/{$method}?{$query}";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, $agent); // pseudo agent
        $json = curl_exec($curl);
        curl_close($curl);

        return $json;
    }

    /**
     * Init game
     */
    function initGame()
    {
        $param = array('_session' => $this->session);
        $json = $this->callApi('gameInit', $param);
        $this->gameData = json_decode($json, true);
        $this->user = $this->gameData['currentUser']['userId'];
        $this->parseLevels();

        return $json;
    }

    /**
     * Play game
     *
     * @param   int     $levelNumber Level number
     */
    function playGame($levelNumber)
    {
        $episodeId = $this->levels[$levelNumber]['episodeId'];
        $levelId = $this->levels[$levelNumber]['levelId'];
        $score = $this->getRandomScore($levelNumber);
        $seed = '1361826675157';
        $secret = 'BuFu6gBFv79BH9hk'; // Secret hash
        $hash = md5("{$episodeId}:{$levelId}:{$score}:-1:{$this->user}:" .
            "{$seed}:{$secret}");
        $hash = substr($hash, 0, 6);

        $arg0 = array(
            'timeLeftPercent' => -1,
            'variant' => 0,
            'reason' => 0,
            'episodeId' => $episodeId,
            'levelId' => $levelId,
            'score' => $score,
            'seed' => $seed,
            'cs' => $hash,
        );
        $param = array('_session' => $this->session,
            'arg0' => json_encode($arg0));

        $json = $this->callApi('gameEnd', $param);
        return $json;
    }

    /**
     * Parse levels
     */
    function parseLevels()
    {
        $i = 0;
        $dreamWorldStart = 1200;
        $episodes = $this->gameData['universeDescription']['episodeDescriptions'];
        foreach ($episodes as $episode) {
            $episodeId = $episode['episodeId'];
            if (!$this->dreamworld) {
                if ($episodeId >= $dreamWorldStart) continue;
            } else {
                if ($episodeId < $dreamWorldStart) continue;
            }
            foreach ($episode['levelDescriptions'] as $level) {
                $i++;
                $levels[$i]['episodeId'] = $level['episodeId'];
                $levels[$i]['levelId'] = $level['levelId'];
                foreach ($level['starProgressions'] as $star) {
                    $levels[$i]["star{$star[numberOfStars]}"] = $star['points'];
                }
            }
        }
        $this->levels = $levels;
    }

    /**
     * Get random score
     *
     * @param   int     $levelNumber Level number
     */
    function getRandomScore($levelNumber)
    {
        $episodeId = $this->levels[$levelNumber]['episodeId'];
        $levelId = $this->levels[$levelNumber]['levelId'];
        $param = array('_session' => $this->session,
            'arg0' => $episodeId, 'arg1' => $levelId);
        $json = $this->callApi('getLevelToplist', $param);
        $jsonArray = json_decode($json, true);
        $toplists = $jsonArray['toplist'];
        $from = $toplists[2]['value'];
        $to = $toplists[0]['value'];
        $score = rand($from, $to);

        return $score;
    }

}