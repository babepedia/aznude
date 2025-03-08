<?php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(E_ALL);


include '../config.php';
include '../functions.php';
include '../libs/simple_html_dom.php';
$mysqli->set_charset('utf8mb4');

$site_url = 'https://babepedia.com/';




if(0)
{

    $names = [];

    $lastpage = '2';
    for ($i = $lastpage; $i > 0; $i--)
    {

        file_put_contents('azncl.txt', $i);
        $url = "https://www.aznude.com/browse/celebs/updated/{$i}.html";
        $resp = func_get_content($url);
        $html = str_get_html($resp);

        $boxs = $html->find("div.container div.row div.celebs-boxes");
        foreach($boxs as $box)
        {
            $link = $box->find('a', 0)->href;
            $slug = basename($link, '.html');
            $name = $box->find('h4',0)->plaintext;

            $namex = $mysqli->real_escape_string($name);
            $slugx = $mysqli->real_escape_string($slug);

            if(in_array($name, $names))
            {

            }else{

                $names[] = $name;
                $xq = $mysqli->query("SELECT * FROM `azcelebs` WHERE `slug` = '{$slugx}' LIMIT 1");
                if($xq->num_rows)
                {

                }else{
                    $mysqli->query("INSERT INTO `azcelebs`( `name`, `slug`, `stat`) VALUES ('{$namex}','{$slugx}','0')");
                }

            }

        }
    }
}

// get celeb data
if(1)
{
    $xq = $mysqli->query("SELECT * FROM `azcelebs` WHERE  pagedata is null order by id desc LIMIT 1000");
    while($row = $xq->fetch_assoc())
    {

        $slug = $row['slug'];
        $fword = $slug[0];
        $url ="https://www.aznude.com/view/celeb/{$fword}/{$slug}.html";
        echo $url."<br/>";
        $resp = func_get_content($url);
        $html = str_get_html($resp);

        $data = [];
        $movies = [];

        if(1)
        {
            $imageElement = $html->find('div.bm-container img.celeb-img', 0);
            $data['image_url'] = $imageElement ? $imageElement->src : null;

// Extract celebrity name
            $nameElement = $html->find('div.celeb-rating h1', 0);

            // Extract birthplace
            $birthplaceElement = $html->find('div.banner-info h1 a', 0);
            $data['birthplace'] = $birthplaceElement ? trim($birthplaceElement->plaintext) : null;
// div.data-urid
            $dataurid = $html->find('div.rw-ui-container', 0);
            if($dataurid)
            {
                $data['urid'] = $dataurid->attr['data-urid'];
                $uridx = $data['urid'];
                echo "-uridx={$uridx}--\n";
                $mysqli->query("UPDATE `azcelebs` SET `urid`='{$uridx}' WHERE id = '{$row['id']}'");
            }


         //   $mboxs = $html->find('div.encolsed-magnific-popup-wrap-up',0);
            foreach ($html->find('section.browse-celeb-main-content') as $section) {

                $container =  $section->next_sibling();
              //  $container = $section->next_sibling()->find('div.movie', 0);

                if (!$section->next_sibling()->find('div.movie', 0)) continue;
                // Extract movie name and year
                $movie_name = trim($section->find('h4 span + a', 0)->plaintext);
                $movie_year = trim(strip_tags($section->find('h4', 0)->plaintext));
                preg_match('/\((\d{4}(?:-\d{0,4})?)\)/', $movie_year, $matches);
                $startyear = 0;
                $endyear = 0;
                if (!empty($matches)) {
                    $startyear = intval($matches[1]); // Capture the start year
                    $endyear = !empty($matches[2]) ? intval($matches[2]) : 0; // Capture the end year if it exists, otherwise set to 0
                }


                $movie_year = $matches[1] ?? null;

                $mvurl = $section->find('h4 a', 0)->href;
                $movieslug = basename($mvurl,'.html');


                $movie_data = [
                    'name' => $movie_name,
                    'year' => $movie_year,
                    'slug' => $movieslug,
                    'media' => []
                ];

                if(1)
                {
                    // check movie
                    $mq = $mysqli->query("SELECT * FROM `azmovies` WHERE slug = '{$movieslug}' LIMIT 1");
                    if(!$mq->num_rows)
                    {
                        $movie_namex = $mysqli->real_escape_string($movie_name);

                       // echo "INSERT INTO `azmovies`(`name`, `slug`, `startyear`, `endyear`) VALUES ('{$movie_namex}','{$movieslug}','{$startyear}','{$endyear}')";
                        $mysqli->query("INSERT INTO `azmovies`(`name`, `slug`, `startyear`, `endyear`) VALUES ('{$movie_namex}','{$movieslug}','{$startyear}','{$endyear}')");
                    }

                }

                // Loop through media items
                foreach ($container->find('div.row div.movie') as $movie) {
                    $link = $movie->find('a', 0);
                    if($link)
                    {

                    }else{
                        continue;
                    }
                    $img = $link->find('img', 0);



                    $vtitle = '';
                    if(isset($img->title))
                    {
                        $vtitle = $img->title;
                    }else{
                        $vtitle = $link->attr['lightbox'];
                    }



                    $movie_data['media'][] = [
                        'type' => strpos($link->class, 'video') !== false ? 'video' : 'image',
                        'url' => $link->href,
                        'thumbnail' => $img->src ?? null,
                        'title' => $vtitle,
                        'duration' => $movie->find('span.video-time', 0)->plaintext ?? null
                    ];
                }

                $movies[] = $movie_data;
            }


        }
        $data['movies'] = $movies;
      //  print_r($data);
        echo "--{$row['id']}--";
        $datax = $mysqli->real_escape_string(json_encode($data));
        $mysqli->query("UPDATE `azcelebs` SET `pagedata`='{$datax}' WHERE id = '{$row['id']}'");
        // echo $mysqli->error;
    }

}