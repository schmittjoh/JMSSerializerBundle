<?php

/*
 * Copyright 2011 Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace JMS\SerializerBundle\Serializer;

class RssSerializationVisitor extends GenericSerializationVisitor
{
    public function getResult()
    {
        $data = $this->getRoot();

        $feed = $data['feed'];

        $out = '<?xml version="1.0" ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>'.$this->escape($feed['title']).'</title>
        <link>'.$this->escape($feed['url']).'</link>
        <description>'.$this->escape($feed['description']).'</description>
        <pubDate>'.date(DATE_RSS).'</pubDate>
        <docs>http://www.rssboard.org/rss-2-0-10</docs>
        <ttl>60</ttl>';

        $pre = "\n        ";

        if (isset($feed['language'])) {
            $out.=$pre.'<language>'.strtr($feed['language'], '_', '-').'</language>';
        }

        if (isset($feed['categories']) && is_array($feed['categories'])) {
            foreach ($feed['categories'] as $category) {
                $out.=$pre.'<category>'.$this->escape($category).'</category>';
            }
        }

        // output items
        foreach ($data['items'] as $item) {
            $out .= $this->outputItem($item);
        }

        // finalize feed
        $out.="\n    </channel>\n</rss>";

        return $out;
    }

    public function outputItem(array $item)
    {
        $out = "\n        <item>";
        $pre = "\n            ";

        $out.=$pre.'<title>'.$this->escape($item['title']).'</title>';
        $out.=$pre.'<description>'.$this->escape($item['description']).'</description>';

        if (isset($item['url'])) {
            $out.=$pre.'<link>'.$this->escape($item['url']).'</link>';
        }
        if (isset($item['publication'])) {
            $publication = $item['publication'];
            if (!$publication instanceof \DateTime) {
                $publication = new \DateTime(preg_match('{^\d{8,}$}', $publication) ? '@'.$publication : $publication);
            }
            $out.=$pre.'<pubDate>'.($publication->format(DATE_RSS)).'</pubDate>';
        }
        if (isset($item['comments'])) {
            $out.=$pre.'<comments>'.$this->escape($item['comments']).'</comments>';
        }
        if (isset($item['author'])) {
            $out.=$pre.'<author>'.$this->escape($item['author']).'</author>';
        }

        if (isset($item['guid'])) {
            if (preg_match('{^https?://}i', $item['guid'])) {
                $out.=$pre.'<guid>'.$this->escape($item['guid']).'</guid>';
            } else {
                $out.=$pre.'<guid isPermaLink="false">'.$this->escape($item['guid']).'</guid>';
            }
        }

        if (isset($item['categories']) && is_array($item['categories'])) {
            foreach ($item['categories'] as $category) {
                $out.=$pre.'<category>'.$this->escape($category).'</category>';
            }
        }

        $out.="\n        </item>";

        return $out;
    }

    protected function escape($text)
    {
        return '<![CDATA['.str_replace(']]>', ']]]]><![CDATA[>', $text).']]>';
    }
}
