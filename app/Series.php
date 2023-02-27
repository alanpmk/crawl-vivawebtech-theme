<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Season;
use App\Episodes;

class Series extends Model
{
    protected $table = 'series';

    protected $fillable = ['series_name','series_poster', 'series_lang_id', 'series_genres', 'upcoming', 'series_access', 'series_slug', 'series_info', 'actor_id', 'director_id', 'imdb_votes'];


	public $timestamps = false;


	public static function getSeriesInfo($id,$field_name)
    {
		$series_info = Series::where('status','1')->where('id',$id)->first();

		if($series_info)
		{
			return  $series_info->$field_name;
		}
		else
		{
			return  '';
		}
	}

	public static function getSeriesTotalSeason($id)
    {
    	$total_season = Season::where('series_id',$id)->count();

    	return $total_season;
    }

	public static function getSeriesTotalEpisodes($id)
    {
    	$total_episode = Episodes::where('episode_series_id',$id)->count();

    	return $total_episode;
    }
}
