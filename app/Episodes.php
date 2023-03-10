<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Series;

class Episodes extends Model
{
	protected $table = 'episodes';

	protected $fillable = [
		'video_title',
		'video_image',
		'episode_series_id',
		'episode_season_id',
		'imdb_votes',
		'video_slug',
		'video_access',
		'video_description', 'video_quality', 'video_type', 'video_url','release_date',
		'created_at', 'updated_at'
	];


	public $timestamps = true;



	public static function getEpisodesInfo($id, $field_name)
	{
		$episodes_info = Episodes::where('status', '1')->where('id', $id)->first();

		if ($episodes_info) {
			return  $episodes_info->$field_name;
		} else {
			return  '';
		}
	}

	public static function getEpisodesShowName($e_id, $s_fieldname)
	{
		$episodes_info = Episodes::where('status', '1')->where('id', $e_id)->first();

		if ($episodes_info) {
			$series_id = $episodes_info->episode_series_id;

			$series_info = Series::where('status', '1')->where('id', $series_id)->first();

			return  $series_info->$s_fieldname;
		} else {
			return  '';
		}
	}
}
