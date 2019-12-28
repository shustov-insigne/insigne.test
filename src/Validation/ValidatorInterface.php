<?php

namespace App\Validation;

interface ValidatorInterface
{
	/**
	 * @param $object
	 *
	 * @return Error|null
	 */
	public function validateBeforeSave($object);

	/**
	 * @param $object
	 *
	 * @return Error|null
	 */
	public function validateBeforeDelete($object);
}