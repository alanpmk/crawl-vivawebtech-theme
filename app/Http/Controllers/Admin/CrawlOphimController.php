<?php

namespace App\Http\Controllers\Admin;

use App\ActorDirector;
use App\Episodes;
use Illuminate\Http\Request;
use Session;
use App\Genres;
use App\Language;
use App\Movies;
use App\Season;
use App\Series;
use DateTime;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;

class CrawlOphimController extends MainAdminController
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }
    public function crawlophimcc()
    {
        $page_title = "Crawl Ophim";
        return view('admin.pages.crawl_ophim', [
            'page_title' => $page_title,
        ]);
    }

    /**
     * Check API to show info films list from Ophim
     *
     * @param  mixed $request
     * @return void
     */
    public function check_nguon(Request $request)
    {
        try {
            $pathname = $request->pathname;
            $origin = $request->origin;
            $full_url = $origin . $pathname;
            $full_response = Http::get($full_url);
            $data =  json_decode($full_response);
            $check_today_update = $this->check_today_update($full_url);
            if (!$data) {
                return response()->json(['code' => 999, 'message' => 'Mẫu JSON không đúng, không hỗ trợ thu thập']);
            }
            $page_array = array(
                'code'              => 1,
                'last_page'         => $data->pagination->totalPages,
                'update_today'      => $check_today_update['film_today'],
                'total'             => $data->pagination->totalItems,
                'full_list_page'    => range(1, $data->pagination->totalPages),
                'latest_list_page'  => range(1, $check_today_update['latest_page']),
            );
            return response()->json($page_array);
        } catch (\Throwable $th) {
            return response()->json(['code' => 999, 'message' => 'Có lỗi! Vui lòng kiểm tra lại']);
        }
    }

    /**
     * Crawl film by id from Ophim
     *
     * @param  mixed $request API url from Ophim
     * @return void json status and message
     */
    public function crawl_by_id(Request $request)
    {
        try {
            //Call api to get films info by id
            $host_name = 'http://ophim1.com/';
            $path_name = $request->api;
            $full_url = $host_name.$path_name;
            $response = Http::get($full_url);
            $data = json_decode($response->body(), true);
            if (!$data['status']) {
                return response()->json(['code' => 999, 'message' => 'Mẫu JSON không đúng, không hỗ trợ thu thập']);
            }
            // return $this->save_image_from_url($data['movie']['thumb_url']);
            $movie_data = $this->refined_data($data);
            if ($movie_data['type'] === "single" || intval($movie_data['episode_total']) === 1 ) { //single_movie
                // Check duplicated from database
                $check_movie_duplicate = Movies::where('imdb_votes', '=', $movie_data['_id'])->first();
                if ($check_movie_duplicate) {
                    $this->update_movie($movie_data);
                    return response()->json(array(
                        'code' => 1,
                        'message' => $movie_data['name']  . ' thể loại ' . $movie_data['type'] . ' : Cập nhật thành công.',
                    ));
                }
                //Insert Movie
                $this->insert_movie($movie_data);
                return response()->json(array(
                    'code' => 1,
                    'message' => $movie_data['name']  . ' thể loại '. $movie_data['type'] . ' : Thu thập thành công.',
                ));
            } else { // series_movies
                //Check duplicated from database
                $check_series = Series::where('imdb_votes', '=', $movie_data['_id'])->first();
                if ($check_series) { // duplicated
                    $check_season = Season::where('series_id', '=', $check_series['id'])->first();
                    //Update series
                    $this->update_series($movie_data,$check_series['id']);
                    // Update movies
                    $this->update_episodes($movie_data, $check_series['id'], $check_season['id']);
                    return response()->json(array(
                        'code' => 1,
                        'message' => $movie_data['name']  . ' thể loại ' . $movie_data['type'] . ' : Cập nhật thành công.',
                    ));
                }
                //Insert movies
                $new_series = $this->insert_series($movie_data);
                $new_season = $this->insert_season($movie_data['poster_url'], $new_series['id']);
                $this->insert_episodes($movie_data, $new_series['id'], $new_season['id']);
                return response()->json(array(
                    'code' => 1,
                    'message' => $movie_data['name']  . ' thể loại ' . $movie_data['type'] . ' : Thu thập thành công.',
                ));
            }
        } catch (\Throwable $e) {
            return response()->json(['code' => 999, 'message' => 'Có lỗi! Vui lòng kiểm tra lại']);
        }
    }

    /**
     * Insert a single movie to movie_videos table
     *
     * @param  mixed $data
     * @return void
     */
    private function insert_movie($data)
    {
        $genres = $this->insert_movies_genres($data['category']);
        $actor = $this->insert_movies_actor_director($data['actor']);
        $director = $this->insert_movies_actor_director($data['director'], 'director');
        $movie_arr = '';
        foreach ($data['server_data'] as $movie) {
            $movie_obj = new Movies;
            $movie_obj->video_access = 'Free';
            $movie_obj->movie_lang_id = $this->insert_movies_language($data['country'][0]['name']);
            $movie_obj->movie_genre_id = $genres;
            $movie_obj->upcoming = 0;
            $movie_obj->video_title = $data['name'] ?? $data['origin_name'];
            $movie_obj->release_date = strtotime($data['year'] . "-" . random_int(1, 12) . "-" . random_int(1, 30));
            $movie_obj->video_description = $data['content'];
            $movie_obj->actor_id = $actor;
            $movie_obj->director_id = $director;
            $movie_obj->video_slug = $data['slug'];
            $movie_obj->video_image = $data['poster_url'] ?? "";
            $movie_obj->video_image_thumb = $data['thumb_url'] ?? $movie_obj->video_image;
            $movie_obj->video_type = 'HLS';
            $movie_obj->video_quality = 0;
            $movie_obj->video_url = $movie['link_m3u8'];
            $movie_obj->imdb_votes = $data['_id']; //Save Ophim movie_id to imdb_votes column
            $movie_obj->save();
            $movie_arr = $movie_obj;
        }
        return $movie_arr;
    }

    /**
     * Update a single movie to movie_videos table
     *
     * @param  mixed $data
     * @return void
     */
    private function update_movie($data)
    {
        $movie_arr = '';
        foreach ($data['server_data'] as $movie) {
            $movie_obj = Movies::updateOrCreate(
                ['imdb_votes' => $data['_id']],
                ['video_url' => $movie['link_m3u8']]
            );
            $movie_arr = $movie_obj;
        }
        return $movie_arr;
    }

    /**
     * Insert a series movies in series table
     *
     * @param  mixed $data
     * @return void
     */
    private function insert_series($data)
    {
        $genres = $this->insert_movies_genres($data['category']);
        $actor = $this->insert_movies_actor_director($data['actor']);
        $director = $this->insert_movies_actor_director($data['director'], 'director');

        $movie_exists = Series::where('imdb_votes', '=', $data['_id'])->first();
        if ($movie_exists) {
            return  $movie_exists;
        }
        $series_obj = new Series;
        $series_obj->series_lang_id = $this->insert_movies_language($data['country'][0]['name']);
        $series_obj->series_genres = $genres;
        $series_obj->upcoming = 0;
        $series_obj->series_access = 'Free';
        $series_obj->series_name = $data['name'];
        $series_obj->series_slug = $data['slug'];
        $series_obj->series_info = $data['content'];
        $series_obj->actor_id = $actor;
        $series_obj->director_id = $director;
        $series_obj->series_poster = ($data['poster_url']);
        $series_obj->imdb_votes = $data['_id']; //Save Ophim movie_id to imdb_votes column
        $series_obj->save();
        return $series_obj;
    }
    private function update_series($data) {
        $series = Series::where('imdb_votes', '=', $data['_id'])->first();
        $series->update(['series_poster'=>$data['poster_url']]);
        Season::where('series_id','=',$series->id)->update(['season_poster'=>$series->series_poster]);
        return $series;
    }

    /**
     * insert a season in season table
     *
     * @param  mixed $image_url
     * @param  mixed $series_id
     * @return void
     */
    private function insert_season($image_url, $series_id)
    {
        $season = Season::firstOrCreate(
            ['series_id' => $series_id],
            ['season_name' => 'Phần 1', 'season_slug' => 'phan-1', 'season_poster' => $image_url, 'status' => 1]
        );
        return $season;
    }

    /**
     * insert episodes to episodes table
     *
     * @param  mixed $data
     * @param  mixed $series_id
     * @param  mixed $season_id
     * @return void
     */
    private function insert_episodes($data, $series_id, $season_id)
    {

        $episodes_arr = array();
        foreach ($data['server_data'] as $episode) {
            $episodes_obj = new Episodes;
            $episodes_obj->video_slug = $data['slug'] . "-" . $episode['slug'];
            $episodes_obj->episode_series_id = $series_id;
            $episodes_obj->episode_season_id = $season_id;
            $episodes_obj->video_access = 'Free';
            $episodes_obj->video_title = "Tập " . $episode['name'] . " - " . "Episode " . $episode['name'];
            $episodes_obj->release_date = strtotime($data['year'] . "-" . random_int(1, 12) . "-" . random_int(1, 30));
            $episodes_obj->video_description = $data['content'];
            $episodes_obj->video_image = ($data['poster_url']);
            $episodes_obj->video_type = 'HLS';
            $episodes_obj->video_quality = 0;
            $episodes_obj->video_url = $episode['link_m3u8'];
            $episodes_obj->imdb_votes = $data['_id'];
            $episodes_obj->save();
            $episodes_arr[] = $episodes_obj;
        }
        return $episodes_arr;
    }

    /**
     * update episodes to episodes table
     *
     * @param  mixed $data
     * @param  mixed $series_id
     * @param  mixed $season_id
     * @return void
     */
    private function update_episodes($data, $series_id, $season_id)
    {
        $episodes_arr = array();
        foreach ($data['server_data'] as $episode) {
            $episodes_obj = Episodes::updateOrCreate(
                ['video_slug' => $data['slug'] . "-" . $episode['slug'], 'imdb_votes' =>  $data['_id']],
                [
                    'episode_series_id' => $series_id,
                    'episode_season_id' => $season_id,
                    'video_access' => 'Free',
                    'video_title' => "Tập " . $episode['name'] . " - " . "Episode " . $episode['name'],
                    'release_date' => strtotime($data['year'] . "-" . random_int(1, 12) . "-" . random_int(1, 30)),
                    'video_description' => $data['content'],
                    'video_image' => ($data['poster_url']),
                    'video_type' => 'HLS',
                    'video_quality' => 0,
                    'video_url' => $episode['link_m3u8'],
                ]
            );
            $episodes_arr[] = $episodes_obj;
        }
        return $episodes_arr;
    }

    /**
     * insert_movies_genres
     *
     * @param  mixed $categories
     * @return void
     */
    private function insert_movies_genres($categories = [])
    {
        $array_id_result = [];
        if (!empty($categories)) {
            foreach ($categories as $category) {
                $genre = Genres::firstOrCreate(
                    ['genre_name' => addslashes($category['name'])],
                    ['genre_slug' => Str::slug($category['name']), 'status' => 1]
                );
                $array_id_result[] = $genre->id;
            }
        }
        return implode(",", $array_id_result);
    }

    /**
     * insert_movies_actor_director
     *
     * @param  mixed $lists list of name
     * @param  mixed $role actor or director
     * @return void
     */
    private function insert_movies_actor_director($lists = [], $role = 'actor')
    {
        $array_id_result = [];

        if (!empty($lists)) {
            foreach ($lists as $list) {
                $result = ActorDirector::firstOrCreate(
                    ['ad_slug' => Str::slug($list)],
                    ['ad_type' => $role, 'ad_name' => $list]
                );
                $array_id_result[] = $result->id;
            }
        }
        return implode(",", $array_id_result);
    }

    /**
     * insert_movies_language
     *
     * @param  mixed $language
     * @return void
     */
    private function insert_movies_language($language)
    {
        $result = Language::firstOrCreate(
            ['language_name' => addslashes($language)],
            ['language_slug' => Str::slug($language), 'status' => 1]
        );
        return $result->id;
    }


    /**
     * get_movies_page action Callback function
     *
     * @param  string $api        url
     * @param  string $param      query params
     * @return json   $page_array List movies in page
     */
    public function get_movies_page(Request $request)
    {
        try {
            $url = $request->api;
            $params = $request->param;
            $url = strpos($url, '?') === false ? $url .= '?' : $url .= '&';

            $response = Http::get($url . $params);

            $data = json_decode($response->body());
            if (!$data) {
                return response()->json(['code' => 999, 'message' => 'Mẫu JSON không đúng, không hỗ trợ thu thập']);
            }
            $page_array = array(
                'code'          => 1,
                'movies'        => $data->items,
            );
            return response()->json($page_array);
        } catch (\Throwable $th) {
            return response()->json(['code' => 999, 'message' => $th]);
        }
    }


    /**
     * Refine movie data from api response
     *
     * @param  array  $array_data   raw movie data
     * @param  array  $movie_data   movie data
     */
    private function refined_data($array_data)
    {
        $movie_data = $array_data['movie'];
        if ($movie_data['type'] == 'hoathinh') {
            array_push($movie_data['category'], (array)["name" => "Hoạt Hình"]);
        }
        if ($movie_data['type'] == 'tvshows') {
            array_push($movie_data['category'], (array)["name" => "TV Shows"]);
        }
        $movie_data['server_data'] = $array_data['episodes'][0]['server_data'];
        $movie_data['thumb_url'] = $this->save_image_from_url($array_data['movie']['thumb_url']);
        $movie_data['poster_url'] = $array_data['movie']['poster_url'] !== "" ? $this->save_image_from_url($array_data['movie']['poster_url']) : $movie_data['thumb_url'];
        return $movie_data;
    }

    private function save_image_from_url($url)
    {
        $url = str_replace('https://', 'http://', $url);
        $img_name = pathinfo($url)['filename'].'.webp';
        if ($this->check_duplicate_img($img_name, 'images')) {
            return 'upload/images/' . $img_name;
        }
        $imageResize  = Image::make($url)->encode('webp',70);
        $imageWidth = $imageResize->width();
        $imageHeight = $imageResize->height();
        if($imageWidth > 1024 || $imageHeight > 1024) {
            if($imageWidth > $imageHeight) {
                $imageResize->resize(1024,null,function($constraint){
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            else {
                $imageResize->resize(null, 1024, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
        }
        $destinationPath = public_path('upload/images/');
        $imageResize->save($destinationPath. $img_name);
        if ($imageResize) {
            return 'upload/images/' . $img_name;
        } else {
            return '';
        }
    }
    /**
     * check images exists in local
     *
     * @param  mixed $img_name
     * @param  mixed $path
     * @return void
     */
    private function check_duplicate_img($img_name, $path)
    {
        return Storage::disk($path)->exists($img_name);
    }

    private function check_today_update($url)
    {
        $url = strpos($url, '?') === false ? $url .= '?' : $url .= '&';
        $today = date("d");
        $film_today = 0;
        $all_items = array();
        $page = 1;
        $istart = true;
        while ($istart) {
            $response = json_decode(Http::get($url . http_build_query(['page' => $page])));
            $data = $response->items;
            foreach ($data as $item) {
                // $movie_update_date = DateTime::createFromFormat('Y-m-d\TH:i:s.vp', $item->modified->time)->format('d');
                $movie_update_date =explode("-",explode("T", $item->modified->time)[0])[2];
                if (intval($movie_update_date) === intval($today)) {
                    $film_today++;
                } else {
                    $istart = false;
                }
            }
            if ($istart === true) {
                $page += 1;
            }
            else {
                break;
            }
        }
        $all_items['film_today'] = $film_today;
        $all_items['latest_page'] = $page;

        return $all_items;
    }
}
