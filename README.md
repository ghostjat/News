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
| 14-01-2023 06:30 | Mahindra launches new range of Thar in Kashmir - Greater Kashmir                                      |   ⇔  | 0.3612  |
| 14-01-2023 04:26 | Global Auto Giant Japan praises Indian Auto Fair - ANI News                                           |   Δ  | 0.6908  |
| 14-01-2023 01:40 | Highlights: HDFC Bank, Avenue Supermarts-owned DMart release Q3FY23 earnings - check profit, revenue  |   ⇔  | 0.4404  |
| 14-01-2023 11:20 | YouTube might soon start streaming TV channels for free - GSMArena.com news - GSMArena.com            |   ⇔  | 0.5106  |
| 14-01-2023 11:11 | ‘People crying in office’: Amazon India employee describes scene amid layoffs - Moneycontrol          |   ∇  | -0.34   |
| 14-01-2023 09:38 | Top 10 SUVs Sold In 2022 - Nexon, Creta, Brezza, Punch, Venue, Seltos - GaadiWaadi.com                |   ⇔  | 0.2023  |
| 14-01-2023 08:50 | The Budget push that can make India's tourism sector one of the world's best - Economic Times         |   Δ  | 0.6369  |
| 14-01-2023 08:45 | Twitter India is emptying out its offices - Moneycontrol                                              |   ∇  | -0.1531 |
| 14-01-2023 08:35 | 27 smallcaps gain 10-34%as market bounces back; midcap index flat - Moneycontrol                      |   ⇔  | 0.5267  |
| 14-01-2023 07:16 | CNG powered Range Rover is actually a New Maruti Brezza in disguise [Video] - CarToq.com              |   ∇  | -0.25   |
| 14-01-2023 05:52 | Flying from Mangaluru? Get ready to shell out more from April - Moneycontrol                          |   ⇔  | 0.3612  |
| 14-01-2023 05:39 | Employee Working Remotely Sues Company But Ordered To Pay $2,600 For Time Theft - NDTV                |   ∇  | -0.1027 |
| 14-01-2023 05:03 | KIA Police Car At Auto Expo 2023 - Rediff.com                                                         |   ∇  | -0.7125 |
| 14-01-2023 03:54 | Paytm down 75%from IPO price. Is this a good entry point for new investors? - Moneycontrol            |   ∇  | -0.296  |
| 14-01-2023 03:44 | Hero Moto Corp starts trial production of flex-fuel motorcycles, as other OEMs showcase vehicles goin |   Δ  | 0.5574  |
| 14-01-2023 03:28 | 8 ‘ChatGPT apps’ on Google Play Store that are ‘fake’ - Gadgets Now                                   |   ⇔  | 0.34    |
| 14-01-2023 12:06 | Traders bid stocks, gold, and Silver higher, but is the optimism warranted? - Kitco NEWS              |   Δ  | 0.6956  |
| 13-01-2023 06:59 | Wipro Limited (WIT) Q3 2023 Earnings Call Transcript - Seeking Alpha                                  |   ∇  | -0.2263 |
| 13-01-2023 06:01 | Google Says Antitrust Penalty A Strike Blow At Digital Adoption In India - NDTV                       |   ∇  | -0.5423 |
| 13-01-2023 05:32 | S&P 500, Nasdaq pare losses as inflation expectations ease By Reuters - Investing.com                 |   ∇  | -0.0516 |
| 13-01-2023 05:18 | Goldman Sachs lost $1.2 billion in 2022 mostly because of Apple Card - AppleInsider                   |   ∇  | -0.3182 |
| 13-01-2023 04:32 | Adidas Loses Stripe Trademark Battle To Luxury Designer Thom Browne - NDTV                            |   ∇  | -0.5994 |
| 13-01-2023 02:19 | NDTV says president, other senior execs resign - The Indian Express                                   |   ∇  | -0.34   |
| 13-01-2023 02:06 | Meet Ravi Kumar, Cognizant's new CEO whose salary is 4 times Mukesh Ambani's 2020 pay; his joining bo |   ⇔  | 0.4767  |
| 13-01-2023 01:00 | China's Oil Demand Is Set To Hit A Record High In 2023 - OilPrice.com                                 |   ∇  | -0.128  |
| 13-01-2023 12:18 | Tech View: Doji candle on Nifty weekly charts shows tug-of-war. What traders should do next week - Ec |   ⇔  | 0.4019  |
| 13-01-2023 12:08 | Tesla cuts EV prices in US amid poor sales - Greatandhra                                              |   ∇  | -0.6486 |
| 13-01-2023 11:59 | Troubles Galore for Vodafone Idea as Indus Towers Threat Resurface - TelecomTalk                      |   ∇  | -0.7506 |
| 13-01-2023 11:02 | Royal Enfield Super Meteor 650 India launch: What to expect? - BikeWale                               |   Δ  | 0.5994  |
| 13-01-2023 10:05 | Kia Carens announced as the Winner of Indian Car of the Year award 2023 - CarToq.com                  |   ⇔  | 0.4767  |
| 13-01-2023 10:03 | Fixed deposits offering up to 9%interest rates — Is it the time to book FDs or wait for more hikes    |   Δ  | 0.6249  |
| 13-01-2023 09:49 | Young founder allegedly sells startup with fake users to JPMorgan for around Rs 1450 crore, now sued  |   ∇  | -0.4767 |
| 13-01-2023 09:36 | Auto Expo 2023: Joy Mihos high-speed e-scooter launched at Rs 1.49 lakh - BikeWale                    |   Δ  | 0.6486  |
| 13-01-2023 09:13 | Amazon Republic Day sale: Dates, how to get best deals and more - Gadgets Now                         |   Δ  | 0.7096  |
| 13-01-2023 08:57 | Apple CEO Tim Cook takes a $35 million pay cut in 2023 - GSMArena.com news - GSMArena.com             |   ∇  | -0.3612 |
| 13-01-2023 07:50 | Govt raids Mukesh Ambanis Hamleys and Archies, seizes 18,000 toys - Free Press Journal                |   ⇔  | 0.5106  |
| 13-01-2023 07:11 | NCLT gives Jalan-Kalrock consortium 6 more months to pay & take control of Jet Airways - Economic Tim |   ∇  | -0.1761 |
| 13-01-2023 07:05 | Zerodha’s Nithin Kamath: It has been a painful bull market for active traders - Moneycontrol          |   ∇  | -0.0516 |
| 13-01-2023 06:59 | Centrum's business heads asked to quit due to 'loss of confidence' - Economic Times                   |   ⇔  | 0.25    |
| 13-01-2023 01:02 | HCCB revives plans to sell Coca-Cola's bottling business - The Economic Times                         |   ⇔  | 0.3818  |
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
