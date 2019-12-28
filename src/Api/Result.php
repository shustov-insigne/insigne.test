<?php

namespace App\Api;

use App\Errors\ErrorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class Result implements NormalizableInterface
{
	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var bool
	 */
	private $success = true;

	/**
	 * @var ErrorInterface
	 */
	private $error;

    /**
     * @var mixed
     */
	private $data;

	/**
	 * Result constructor.
	 * @param string $type
	 */
	public function __construct(string $type = '')
	{
		$this->type = $type;
	}

	/**
	 * @param string $type
	 * @return $this
	 */
	public function setType(string $type)
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * @param ErrorInterface $error
	 * @return $this
	 */
	public function setError(ErrorInterface $error)
	{
		$this->success = false;
		$this->error = $error;

		return $this;
	}

	/**
	 * @param NormalizerInterface $normalizer
	 * @param null $format
	 * @param array $context
	 *
	 * @return array
	 *
	 * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
	 */
	public function normalize(NormalizerInterface $normalizer, $format = null, array $context = [])
	{
		$result = [
			'type' => $this->type,
			'success' => $this->success,
            'data' => $normalizer->normalize($this->data, $format, $context),
		];

		if (!$this->success) {
			$result['error'] = $normalizer->normalize($this->error, $format, $context);
		}

		return $result;
	}

    /**
     * @param mixed $data
     * @return Result
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}