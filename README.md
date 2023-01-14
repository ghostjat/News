# News Sentiment Analysis

VADER (Valence Aware Dictionary and sEntiment Reasoner) is a lexicon and rule-based sentiment analysis tool that is used in social media.

## Example code

```php
require 'vendor/autoload.php';

use Core\News;
use Core\Sentiments\Analyzer;

$table = new Console_Table();
$sentiment = new Analyzer();
$news = new News();
$data = $news->getTopHeadLines(country: 'in');
$analyisData = [];
foreach ($data->articles as $key => $value) {
    $res = $sentiment->getSentiment($value->title);
    if($res['res'] > 0.55 ) {
        $analyisData[] = [
            $news->date($value->publishedAt),
            $table->green($news->limitedString($value->title)),
            $table->green($table->bold($news->icon('up'))),
            $table->green($res['res'])
        ];
    }
    
    if($res['res'] > 0 && $res['res'] < 0.55 ) {
        $analyisData[] = [
            $news->date($value->publishedAt),
            $table->blue($news->limitedString($value->title)),
            $table->blue($table->bold($news->icon('nu'))),
            $table->blue($res['res'])
        ];
    }
    
    if($res['res'] < 0 ) {
       $analyisData[] = [
            $news->date($value->publishedAt),
            $table->red($news->limitedString($value->title)),
            $table->red($table->bold($news->icon('dw'))),
            $table->red($res['res'])
        ];
    }
}
echo $table->fromArray(['Date','Title','View','Score'], $analyisData);
```
## Output of example code
```
+------------------+-------------------------------------------------------------------------------------------------------+------+---------+
| Date             | Title                                                                                                 | View | Score   |
+------------------+-------------------------------------------------------------------------------------------------------+------+---------+
| 14-01-2023 05:14 | Watch: Shah Rukh Khan's 'Pathaan' trailer lights up Burj Khalifa - Khaleej Times                      | ⇔  | 0.2732  |
| 14-01-2023 04:50 | CBI At My Office, Tweets Arvind Kejriwal's Deputy; Agency Says No Raid - NDTV                         | ∇  | -0.296  |
| 14-01-2023 03:48 | Cold Wave Redux Won't Freeze Delhi But Temperatures May Drop To... - NDTV                             | ∇  | -0.2866 |
| 14-01-2023 03:33 | 2 BILLION Google Chrome users hit by browser security flaw! Protect yourself now - HT Tech            | Δ  | 0.6476  |
| 14-01-2023 03:18 | DMK's Shivaji Krishnamoorthy suspended for remarks against governor RN Ravi - Hindustan Times         | ∇  | -0.4767 |
| 14-01-2023 03:15 | Watch: Twitter Fumes Over Bruno Fernandes' Controversial Goal In Manchester Derby - NDTV Sports       | ∇  | -0.2263 |
| 14-01-2023 02:59 | ‘3,500 hours of torture, forced me…’: British-Iranian man before execution - Hindustan Times          | ∇  | -0.7845 |
| 14-01-2023 02:44 | Video: Delhi Man Dragged For Half-A-Kilometre On Car Bonnet - NDTV                                    | ∇  | -0.0516 |
| 14-01-2023 01:40 | Highlights: HDFC Bank, Avenue Supermarts-owned DMart release Q3FY23 earnings - check profit, revenue  | ⇔  | 0.4404  |
| 14-01-2023 01:15 | Star shaped into a Donut by black hole! Terrifying snap taken by NASA's Hubble Telescope - HT Tech    | ∇  | -0.6114 |
| 14-01-2023 12:59 | At least 2 feared dead, 10 hurt as Odisha's longest bridge sees stampede - Hindustan Times            | ∇  | -0.9287 |
| 14-01-2023 12:35 | Chopped Up Body Found In Delhi Day After 2 Arrested Over Terror Links: Sources - NDTV                 | ∇  | -0.6597 |
| 14-01-2023 12:18 | Hockey WC: India expect tougher outing against England - Rediff.com                                   | ⇔  | 0.1779  |
| 14-01-2023 12:12 | Rajasthan engineer suspended for trying to touch President Murmu's feet | Video - Hindustan Times     | ∇  | -0.4767 |
| 14-01-2023 11:45 | Samsung Galaxy S23 Ultra, Galaxy S23 Plus design images leaked ahead of February 1 event - Moneycontr | ∇  | -0.3182 |
| 14-01-2023 10:22 | China Covid scare: All of Beijing residents likely to get infected by end of January, claims study -  | ∇  | -0.7506 |
| 14-01-2023 09:15 | 'Rohit has made it clear': India great's blunt reaction to Ishan, SKY's ODI snub - Hindustan Times    | ∇  | -0.4215 |
| 14-01-2023 08:36 | WHO urges South-East Asian countries to take urgent and accelerated measures against measles | - News | ⇔  | 0.2023  |
| 14-01-2023 07:50 | 'No veteran like Bhishma Pitamah...': Rajnath Singh as he praises soldiers - Hindustan Times          | Δ  | 0.5719  |
| 14-01-2023 05:03 | KIA Police Car At Auto Expo 2023 - Rediff.com                                                         | ∇  | -0.7125 |
| 14-01-2023 05:02 | No link between sinking of Joshimath and NTPC tunnel projects, Centre says | Mint - Mint              | ∇  | -0.296  |
| 14-01-2023 02:35 | Bigg Boss 16: Shiv Thakare cries uncontrollably, MC Stan, Nimrit Kaur Ahluwalia break down after Abdu | ∇  | -0.7506 |
| 13-01-2023 06:59 | Wipro Limited (WIT) Q3 2023 Earnings Call Transcript - Seeking Alpha                                  | ∇  | -0.2263 |
| 13-01-2023 01:02 | Slim Keto Gummies Reviews [Scam Exposed] Slim Candy Keto Gummies | Side Effects ALERT Must Read Befor | ⇔  | 0.4466  |
| 13-01-2023 12:00 | ECDC assesses risk to the EU/EEA associated with Omicron XBB1.5 sub-lineage - European Centre for Dis | ∇  | -0.2732 |
| 13-01-2023 08:38 | Trial by Fire Review: Gripping - Rediff.com                                                           | ∇  | -0.34   |
| 13-01-2023 06:13 | US Flight Glitch Caused By Personnel Who Damaged Data File - NDTV                                     | ∇  | -0.4404 |
+------------------+-------------------------------------------------------------------------------------------------------+------+---------+

```

## Copyright and license

The original source code is copyright © 2013 C.J. Hutto

Where applicable, the ported source code is copyright © 2016 Andrew Busby. All rights reserved. The ported code is made available under the MIT license. A copy of the license can be found in the LICENSE.txt file.

## Acknowledgments

This is a php port of the vader sentiment analysis tool orginally written in python and found https://github.com/cjhutto/vaderSentiment

## This README file describes the dataset of the paper:

VADER: A Parsimonious Rule-based Model for Sentiment Analysis of Social Media Text 
(by C.J. Hutto and Eric Gilbert) 
Eighth International Conference on Weblogs and Social Media (ICWSM-14). Ann Arbor, MI, June 2014. 

## Citation Information

If you use either the dataset or any of the VADER sentiment analysis tools (VADER sentiment lexicon or Python code for rule-based sentiment analysis engine) in your research, please cite the above paper. For example: 

Hutto, C.J. & Gilbert, E.E. (2014). VADER: A Parsimonious Rule-based Model for Sentiment Analysis of Social Media Text. Eighth International Conference on Weblogs and Social Media (ICWSM-14). Ann Arbor, MI, June 2014. 

## Fork Maintainer

This repository is maintained by [ghostjat](https://github.com/ghostjat)
