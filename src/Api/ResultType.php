<?php

namespace App\Api;

abstract class ResultType
{
	const ADD = 'add';
	const UPDATE = 'update';
	const DELETE = 'delete';
	const READ = 'read';
	const OTHER = 'other';
}