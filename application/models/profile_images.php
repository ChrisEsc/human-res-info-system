<?php

require_once "my_model.php";
class Profile_Images extends My_Model {

	const DB_TABLE = 'profile_images';
	const DB_TABLE_PK = 'id';

	public $id;
	public $profile_id;
	public $src;
	public $type;
}