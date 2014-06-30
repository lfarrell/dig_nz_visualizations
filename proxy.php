<?php
class ProxyNZ {


    /**
     * @param int $page
     * @param string $decade_start
     * @param string $decade_end
     * @return mixed
     */
    private function base_call($page) {
        $full_call = 'http://api.digitalnz.org/v3/records.json?api_key=9mgrhqKqmNiCmCasck4b&text=earthquake&and[category][]=Images&and[content_partner][]=UC+QuakeStudies&fields=title,source_url,thumbnail_url,creator,date&facets=creator,placename,year&per_page=100&page=' . $page;

        $ch = curl_init($full_call);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

    private function sample() {
        $pages = range(1,204);
        shuffle($pages);

        return array_rand($pages, 2);
    }

    public function process() {
        $images = array();
        $facets = array();
        $combined = array();
        $pages = $this->sample();

        foreach($pages as $page) {
            $imgs = json_decode($this->base_call($page), true);
            $results = $imgs['search']['results'];

            $images = array_merge($images, $results);

            // Create facets
            foreach($results as $result) {
                $contrib = $result['creator'][0];
                preg_match('/^\d{4}/', $result['date'][0], $matches);

                if(array_key_exists($contrib, $facets)) {
                    $facets[$contrib] += 1;
                } elseif($contrib == '') {
                    // nothing
                } else {
                    $facets[$contrib] = 1;
                }

                if(array_key_exists($matches[0], $facets)) {
                    $facets[$matches[0]] += 1;
                } elseif($matches[0] == '') {
                    // nothing
                } else {
                    $facets[$matches[0]] = 1;
                }
            }

           // if($key == count($pages) - 1) { $facets['facets'] = $imgs['search']['facets']; }
        }

        $combined['photos'] = $images;
        $combined['facets'] = [$facets];

        echo json_encode($combined);
    }
}

$imgs = new ProxyNZ($_GET['page']);
$imgs->process();