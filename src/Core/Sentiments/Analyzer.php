<?php
declare (strict_types=1);
namespace Core\Sentiments;

use Core\Sentiments\Config\Config;
use Core\Sentiments\Procedures\SentiText;

/*
  Give a sentiment intensity score to sentences.
 */

class Analyzer {

    private $lexicon_file = "";
    private $lexicon = "";
    public $emoji_lexicon, $emojis;
    private $current_sentitext = null;

    public function __construct(string $lexicon_file = "Lexicons/vader_sentiment_lexicon.txt", string $emoji_lexicon = 'Lexicons/emoji_utf8_lexicon.txt') {
        $this->lexicon_file = __DIR__ . DIRECTORY_SEPARATOR . $lexicon_file;
        $this->lexicon = $this->make_lex_dict();
        $this->emoji_lexicon = __DIR__ . DIRECTORY_SEPARATOR . $emoji_lexicon;
        $this->emojis = $this->make_emoji_dict();
    }
    
    /**
     * 
     * @param string $type dw|up|nu
     * @return string
     */
    public function icon(string $type):string {
        switch ($type) {
            case 'dw':
                $icon = html_entity_decode('&#8711;');
                break;
            case 'up':
                $icon = html_entity_decode('&#916;');
                break;
            case 'nu':
                $icon = html_entity_decode('&hArr;');
                break;
        }
        return $icon;
    } 
    
    /**
     * Determine if input contains negation words
     * @param string $wordToTest
     * @param bool $include_nt
     * @return boolean
     */
    public function IsNegated(string $wordToTest, bool $include_nt = true):bool {
        $wToTest = strtolower($wordToTest);
        if (in_array($wToTest, Config::NEGATE)) {
            return true;
        }

        if ($include_nt) {
            if (strpos($wToTest, "n't")) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert lexicon file to a dictionary
     * @return array
     */
    public function make_lex_dict():array {
        $lex_dict = [];
        $fp = fopen($this->lexicon_file, "r");
        if (!$fp) {
            die("Cannot load lexicon file");
        }
        while (($line = fgets($fp, 4096)) !== false) {
            list($word, $measure) = explode("\t", trim($line));
            $lex_dict[$word] = $measure;
        }
        return $lex_dict;
    }
    
    /**
     * 
     * @return array
     */
    public function make_emoji_dict():array {
        $emoji_dict = [];
        $fp = fopen($this->emoji_lexicon, "r");
        if (!$fp) {
            die("Cannot load emoji lexicon file");
        }

        while (($line = fgets($fp, 4096)) !== false) {
            list($emoji, $description) = explode("\t", trim($line));
            $emoji_dict[$emoji] = $description;
        }
        return $emoji_dict;
    }

    /**
     * 
     * @param type $arr
     * @return type
     */
    public function updateLexicon(array $arr) {
        if (!is_array($arr)) {
            return false;
        }
        foreach ($arr as $word => $valence) {
            $this->lexicon[strtolower($word)] = is_numeric($valence) ? $valence : 0;
        }
    }
    
    /**
     * Return a float for sentiment strength based on the input text.
     * Positive values are positive valence, 
     * negative value are negative valence
     * @param string $text
     * @return array
     */

    public function getSentiment(string $text):array {

        $text_no_emoji = '';
        $prev_space = true;

        foreach ($this->str_split_unicode($text) as $unichr) {
            if (array_key_exists($unichr, $this->emojis)) {
                $description = $this->emojis[$unichr];
                if (!($prev_space)) {
                    $text_no_emoji .= ' ';
                }
                $text_no_emoji .= $description;
                $prev_space = false;
            } else {
                $text_no_emoji .= $unichr;
                $prev_space = ($unichr == ' ');
            }
        }
        $text = trim($text_no_emoji);

        $this->current_sentitext = new SentiText($text);

        $sentiments = [];
        $words_and_emoticons = $this->current_sentitext->words_and_emoticons;

        for ($i = 0; $i <= count($words_and_emoticons) - 1; $i++) {
            $valence = 0.0;
            $wordBeingTested = $words_and_emoticons[$i];

            if ($this->IsInLexicon($wordBeingTested)) {

                if ("kind" != $words_and_emoticons[$i] && "of" != $words_and_emoticons[$i]) {
                    $valence = $this->getValenceFromLexicon($wordBeingTested);

                    $wordInContext = $this->getWordInContext($words_and_emoticons, $i);
                    $valence = $this->adjustBoosterSentiment($wordInContext, $valence);
                }
            }
            array_push($sentiments, $valence);
        }
        $sentiments = $this->_but_check($words_and_emoticons, $sentiments);

        return $this->_score_valence($sentiments, $text);
    }
    
    /**
     * 
     * @param string $firstWord
     * @param string $secondWord
     * @return type
     */
    private function IsKindOf(string $firstWord, string $secondWord) {
        return "kind" === strtolower($firstWord) && "of" === strtolower($secondWord);
    }
    
    /**
     * 
     * @param type $word
     * @return type
     */
    private function IsBoosterWord($word) {
        return array_key_exists(strtolower($word), Config::BOOSTER_DICT);
    }

    private function getBoosterScaler($word) {
        return Config::BOOSTER_DICT[strtolower($word)];
    }

    private function IsInLexicon($word) {
        $lowercase = strtolower($word);

        return array_key_exists($lowercase, $this->lexicon);
    }

    private function IsUpperCaseWord($word) {
        return ctype_upper($word);
    }

    private function getValenceFromLexicon($word) {
        return $this->lexicon[strtolower($word)];
    }

    private function getTargetWordFromContext($wordInContext) {
        return $wordInContext[count($wordInContext) - 1];
    }

    /*
      Gets the precedding two words to check for emphasis
     */

    private function getWordInContext($wordList, $currentWordPosition) {
        $precedingWordList = [];

        //push the actual word on to the context list
        array_unshift($precedingWordList, $wordList[$currentWordPosition]);
        //If the word position is greater than 2 then we know we are not going to overflow
        if (($currentWordPosition - 1) >= 0) {
            array_unshift($precedingWordList, $wordList[$currentWordPosition - 1]);
        } else {
            array_unshift($precedingWordList, "");
        }

        if (($currentWordPosition - 2) >= 0) {
            array_unshift($precedingWordList, $wordList[$currentWordPosition - 2]);
        } else {
            array_unshift($precedingWordList, "");
        }

        if (($currentWordPosition - 3) >= 0) {
            array_unshift($precedingWordList, $wordList[$currentWordPosition - 3]);
        } else {
            array_unshift($precedingWordList, "");
        }

        return $precedingWordList;
    }

    private function str_split_unicode($str, $l = 0) {
        if ($l > 0) {
            $ret = array();
            $len = mb_strlen($str, "UTF-8");
            for ($i = 0; $i < $len; $i += $l) {
                $ret[] = mb_substr($str, $i, $l, "UTF-8");
            }
            return $ret;
        }
        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
    }

    private function applyValenceCapsBoost($targetWord, $valence) {
        if ($this->IsUpperCaseWord($targetWord) && $this->current_sentitext->is_cap_diff) {
            if ($valence > 0) {
                $valence += Config::C_INCR;
            } else {
                $valence -= Config::C_INCR;
            }
        }

        return $valence;
    }

    private function boosterScaleAdjustment($word, $valence) {
        $scalar = 0.0;
        if (!$this->IsBoosterWord($word)) {
            return $scalar;
        }

        $scalar = $this->getBoosterScaler($word);

        if ($valence < 0) {
            $scalar *= -1;
        }
        $scalar = $this->applyValenceCapsBoost($word, $scalar);

        return $scalar;
    }

    private function dampendBoosterScalerByPosition($booster, $position) {
        if (0 === $booster) {
            return $booster;
        }

        if (1 == $position) {
            return $booster * 0.95;
        }

        if (2 == $position) {
            return $booster * 0.9;
        }

        return $booster;
    }

    private function adjustBoosterSentiment($wordInContext, $valence) {
        //The target word is always the last word
        $targetWord = $this->getTargetWordFromContext($wordInContext);
        $valence = $this->applyValenceCapsBoost($targetWord, $valence);
        $valence = $this->modifyValenceBasedOnContext($wordInContext, $valence);
        return $valence;
    }

    private function modifyValenceBasedOnContext($wordInContext, $valence) {
        $wordToTest = $this->getTargetWordFromContext($wordInContext);
        for ($i = 0; $i < count($wordInContext) - 1; $i++) {
            $scalarValue = $this->boosterScaleAdjustment($wordInContext[$i], $valence);
            $scalarValue = $this->dampendBoosterScalerByPosition($scalarValue, $i);
            $valence = $valence + $scalarValue;
        }
        $valence = $this->_never_check($wordInContext, $valence);
        $valence = $this->_idioms_check($wordInContext, $valence);
        $valence = $this->_least_check($wordInContext, $valence);
        return $valence;
    }

    private function _least_check($wordInContext, $valence) {
        if (strtolower($wordInContext[2]) == "least") {
            if (strtolower($wordInContext[1]) != "at" && strtolower($wordInContext[1]) != "very") {
                $valence = $valence * Config::N_SCALAR;
            }
        }

        return $valence;
    }

    private function _but_check($words_and_emoticons, $sentiments) {
        $bi = array_search("but", $words_and_emoticons);
        if (!$bi) {
            $bi = array_search("BUT", $words_and_emoticons);
        }
        if ($bi) {
            for ($si = 0; $si < count($sentiments); $si++) {
                if ($si < $bi) {
                    $sentiments[$si] = $sentiments[$si] * 0.5;
                } else if ($si > $bi) {
                    $sentiments[$si] = $sentiments[$si] * 1.5;
                }
            }
        }

        return $sentiments;
    }

    private function _idioms_check($wordInContext, $valence) {
        $onezero = sprintf("%s %s", $wordInContext[2], $wordInContext[3]);
        $twoonezero = sprintf("%s %s %s", $wordInContext[1], $wordInContext[2], $wordInContext[3]);
        $twoone = sprintf("%s %s", $wordInContext[1], $wordInContext[2]);
        $threetwoone = sprintf("%s %s %s", $wordInContext[0], $wordInContext[1], $wordInContext[2]);
        $threetwo = sprintf("%s %s", $wordInContext[0], $wordInContext[1]);
        $zeroone = sprintf("%s %s", $wordInContext[3], $wordInContext[2]);
        $zeroonetwo = sprintf("%s %s %s", $wordInContext[3], $wordInContext[2], $wordInContext[1]);
        $sequences = [$onezero, $twoonezero, $twoone, $threetwoone, $threetwo];

        foreach ($sequences as $seq) {
            $key = strtolower($seq);
            if (array_key_exists($key, Config::SPECIAL_CASE_IDIOMS)) {
                $valence = Config::SPECIAL_CASE_IDIOMS[$key];
                break;
            }
            if ($this->IsBoosterWord($threetwo) || $this->IsBoosterWord($twoone)) {
                $valence = $valence + Config::B_DECR;
            }
        }

        return $valence;
    }

    private function _never_check($wordInContext, $valance) {
        $neverModifier = 0;
        if ("never" == $wordInContext[0]) {
            $neverModifier = 1.25;
        } else if ("never" == $wordInContext[1]) {
            $neverModifier = 1.5;
        }
        if ("so" == $wordInContext[1] || "so" == $wordInContext[2] || "this" == $wordInContext[1] || "this" == $wordInContext[2]) {
            $valance *= $neverModifier;
        }

        foreach ($wordInContext as $wordToCheck) {
            if ($this->IsNegated($wordToCheck)) {
                $valance *= Config::B_DECR;
            }
        }

        return $valance;
    }

    private function _sentiment_laden_idioms_check($valence, $senti_text_lower) {
        $idioms_valences = [];
        foreach (Config::SENTIMENT_LADEN_IDIOMS as $idiom) {
            if (in_array($idiom, $senti_text_lower)) {
                $valence = Config::SENTIMENT_LADEN_IDIOMS[$idiom];
                $idioms_valences[] = $valence;
            }
        }

        if ((strlen($idioms_valences) > 0)) {
            $valence = ( array_sum(explode(',', $idioms_valences)) / floatval(strlen($idioms_valences)));
        }
        return $valence;
    }

    private function _punctuation_emphasis($sum_s, $text) {
        // add emphasis from exclamation points and question marks
        $ep_amplifier = $this->_amplify_ep($text);
        $qm_amplifier = $this->_amplify_qm($text);
        $punct_emph_amplifier = $ep_amplifier + $qm_amplifier;

        return $punct_emph_amplifier;
    }

    private function _amplify_ep($text) {
        $ep_count = substr_count($text, "!");
        if ($ep_count > 4) {
            $ep_count = 4;
        }
        $ep_amplifier = $ep_count * 0.292;

        return $ep_amplifier;
    }

    private function _amplify_qm($text) {
        $qm_count = substr_count($text, "?");
        $qm_amplifier = 0;
        if ($qm_count > 1) {
            if ($qm_count <= 3) {
                $qm_amplifier = $qm_count * 0.18;
            } else {
                $qm_amplifier = 0.96;
            }
        }

        return $qm_amplifier;
    }

    private function _sift_sentiment_scores($sentiments) {
        $pos_sum = 0.0;
        $neg_sum = 0.0;
        $neu_count = 0;
        foreach ($sentiments as $sentiment_score) {
            if ($sentiment_score > 0) {
                $pos_sum += $sentiment_score + 1;
            }
            if ($sentiment_score < 0) {
                $neg_sum += $sentiment_score - 1; 
            }
            if ($sentiment_score == 0) {
                $neu_count += 1;
            }
        }

        return [$pos_sum, $neg_sum, $neu_count];
    }

    private function _score_valence($sentiments, $text) {
        if ($sentiments) {
            $sum_s = array_sum($sentiments);
            $punct_emph_amplifier = $this->_punctuation_emphasis($sum_s, $text);
            if ($sum_s > 0) {
                $sum_s += $punct_emph_amplifier;
            } elseif ($sum_s < 0) {
                $sum_s -= $punct_emph_amplifier;
            }

            $compound = Config::normalize($sum_s);
            list($pos_sum, $neg_sum, $neu_count) = $this->_sift_sentiment_scores($sentiments);

            if ($pos_sum > abs($neg_sum)) {
                $pos_sum += $punct_emph_amplifier;
            } elseif ($pos_sum < abs($neg_sum)) {
                $neg_sum -= $punct_emph_amplifier;
            }

            $total = $pos_sum + abs($neg_sum) + $neu_count;
            $pos = abs($pos_sum / $total);
            $neg = abs($neg_sum / $total);
            $neu = abs($neu_count / $total);
        } else {
            $compound = 0.0;
            $pos = 0.0;
            $neg = 0.0;
            $neu = 0.0;
        }

        $sentiment_dict = ["neg" => round($neg, 3),
                    "neu" => round($neu, 3),
                    "pos" => round($pos, 3),
                    "res" => round($compound, 4)];

        return $sentiment_dict;
    }

}
