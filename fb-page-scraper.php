<?php
/**
 * Fb public page post scraper
 * 
 * @author Jaydeep Chauhan<jd.dev777@gmail.com>
 * 
 * @param string          $page_name
 * @return array
 */
function fbPageScraper( $page_name = '' )  {
    $post_url = "https://www.facebook.com/pg/{$page_name}/posts";
    
    $ch = curl_init($post_url);
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.7.12) Gecko/20050915 Firefox/1.0.7");
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $contents = curl_exec($ch);
    
    $doc = new DOMDocument();
    $doc->loadHTML($contents);
    $xpath = new DOMXPath($doc);
    $query = "//div[@data-testid='post_message']";
    $entries = $xpath->query($query);
    
    $posts = array();
    foreach ($entries as $entry) {
        $innerHTML = '';
        if ($entry->getElementsByTagName('p')->length) {
            foreach ($entry->getElementsByTagName('p') as $child) {
                $innerHTML .= $child->ownerDocument->saveXML($child);
            }
        }
    
        $images = array();
        if ($entry->nextSibling && $entry->nextSibling->getElementsByTagName('img')->length) {
            foreach ($entry->nextSibling->getElementsByTagName('img') as $image) {
                $images[] = $image->getAttribute('src');
            }
        }
    
        $href = array();
        if ($entry->nextSibling && $entry->nextSibling->getElementsByTagName('a')->length) {
            foreach ($entry->nextSibling->getElementsByTagName('a') as $anchor) {
                if (strpos($anchor->getAttribute('href'), 'https://l.facebook.com') === false) {
                    $href[] = 'https://www.facebook.com' . $anchor->getAttribute('href');
                } else {
                    $href[] = $anchor->getAttribute('href');
                }
    
            }
        }
    
        $posts[] = array(
            'content' => $innerHTML,
            'images' => count($images) ? $images[0] : '',
            'href' => count($href) ? $href[0] : '',
        );
    }

    return $posts;
}
