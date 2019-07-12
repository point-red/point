<?php

namespace App\Model;

interface Rewardable
{
	public function getPointAmount() : int;
}