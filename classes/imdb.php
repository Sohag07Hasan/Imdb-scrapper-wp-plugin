<?php
// define constants
define('NO_POSTER','Poster is not available.');
define('INVALID_URL','This URL is invalid. Please try again.');
    class imdbInfo
    {
		private $imdbUrl = null;
		
        function __construct() {
			include IMDB_INCLUDES . '/utilities.php';
        }
		// control the link is valid or not
		private function linkController() {
			$result = true;
			$imdbId = $this->match('/imdb.com\/title\/(tt[0-9]+)/ms', $this->imdbUrl, 1);
			if($imdbId != "") {
				$this->imdbUrl = 'http://www.imdb.com/title/'.$imdbId.'/';
			} else {
				$result = false;
			}
			return $result;
		}

		// get data from IMDB
		function getDataFromIMDB($url) {
			$this->imdbUrl = $url;
            $result = array();
			$linkControl = $this->linkController();
			if ($linkControl) {
				$html = $this->getContent();

			$result = array();
				
			$result['id'] = $this->match('/poster.*?(tt[0-9]+)/ms', $html, 1);
			$result['type'] = $this->match('/<meta.*?property=.og:type.*?content=.(.*?)(\'|")/ms', $html, 1);					
				
			$result['title'] = $this->match('/<title>(.*?)<\/title>/ms', $html, 1);
			$result['title'] = $this->match('/(.*?) - IMDb/ms', $result['title'], 1);
			$numbersInTitle = $this->match_all('/\((.*?)\)/', $result['title'], 1);
			$result['title'] = preg_replace('/\([0-9]+\)/', '', $result['title']);
			$result['title'] = trim($result['title']);
			if (count($numbersInTitle) == 1) {
				$result['year'] = $numbersInTitle[0];
			} else if (count($numbersInTitle) == 2) {
				$result['year'] = $numbersInTitle[1];		
				$result['title'] = '('.$numbersInTitle[0].') '.$result['title']; 
			}
			
			$result['release_date'] = $this->match('/([0-9][0-9]? (January|February|March|April|May|June|July|August|September|October|November|December) (19|20)[0-9][0-9])/ms', $html, 1);
			$rel = explode(' ',$result['release_date']);
			$result['day'] = $rel[0];
			$result['month'] = $rel[1];
			$result['year'] = $rel[2];
			
			$result['poster'] = $this->match('/<img.*?src="http:\/\/ia.media-imdb.com\/images\/(.*?)".*?Poster.*?/ms', $html, 1);	
			$result['poster'] = 'http://ia.media-imdb.com/images/'.$result['poster'];	  
			if ($result['poster'] == "") {
				$result['poster'] = NO_POSTER;
			}					
							
				
				
				$result['name'] = regex_get('#<h1.*?>(.*?)<span#msi', $html, 1);
				$result['desc'] = regex_get('#"description">(.*?)</p>#msi', $html, 1);
				$date = regex_get('#datetime="(\d+)#msi', $html, 1, 'num');
				if (empty($date)) {
					$date = clean_num(regex_get('#<title>[^\(]*\(([^\)]+)\)#msi', $html, 1, 'num'));
				}
				$result['date'] = $date;
				$result['duration'] = regex_get('#class="absmiddle"[^<]*?(\d+\s*min)#msi', $html, 1);
				$result['duration'] = preg_replace('/[^0-9.]/','',$result['duration']);
				
				// Only for Movies
				$result['director'] = regex_get('#writer.*?([\s\w]*)</a#msi', $html, 1);
				$result['writer'] = regex_get('#writer.*?([\s\w]*)</a#msi', $html, 1);
				// Only for TV shows
				$result['creator'] = regex_get('#creator.*?([\s\w]*)</a#msi', $html, 1);
				
				$result['cast'] = array();
				if (preg_match_all('#class="name".*?>([^<]*)</a>#msi', $html, $cast)) {
					$result['cast'] = $cast[1];
				}
				
				$result['genres'] = array();
				if (preg_match_all('#/genre/([^"]*)"\s*>\1#msi', $html, $genre)) {
					$result['genres'] = $genre[1];
				}
				
				$result['plot'] = regex_get('#storyline</h2>\s*<p>(.*?)<#msi', $html, 1);
				
				$result['rating'] = regex_get('#"ratingValue">(.*?)<#msi', $html, 1, 'num');
				$result['max-rating'] = regex_get('#"bestRating">(.*?)<#msi', $html, 1, 'num');
				$result['voter-count'] = regex_get('#"ratingCount">(.*?)<#msi', $html, 1, 'num');
				$result['user-review-count'] = regex_get('#"reviewCount">(.*?)<#msi', $html, 1, 'num');
				$result['critic-review-count'] = regex_get('#(\d+) external critic#msi', $html, 1, 'num');
				
				return $result;				
				
			} 
			
			else {
				$result['error'] = INVALID_URL;
			}
		//	$result['title'] = htmlspecialchars($html);
            return $result;
        }		
		// get content of target IMDB url
        private function getContent() {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->imdbUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            $html = curl_exec($ch);
            curl_close($ch);
            return $html;
        }
		// for regular expression call
        private function match_all($regex, $str, $i = 0) {
            if(preg_match_all($regex, $str, $matches) === false)
                return false;
            else
                return $matches[$i];

        }
		// for regular expression call
        private function match($regex, $str, $i = 0) {
            if(preg_match($regex, $str, $match) == 1)
                return $match[$i];
            else
                return false;
        }
       
               
    }
?>
