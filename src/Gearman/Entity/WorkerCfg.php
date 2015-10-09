<?php

namespace Hunter\Gearman\Entity;
use Respect\Validation\Validator;

/**
 * This class is a representation of Gearman's worker configuration.
 * @Entity
 * @Table(name="gearman_workers", schema="config")
 */
class WorkerCfg {
	/**
	 * @var Integer internal system ID
	 * @Id @Column(type="integer", nullable=false) @GeneratedValue
	 */
	private $id;

	/**
	 * @var String $name is the name of worker.
	 * @Column(type="string", nullable=false, options={"unique": true})
	 */
	private $name;

	/**
	 * @var Integer $min is the minimum number of workers.
	 * @Column(type="smallint", nullable=false, options={"default": 1, "unsigned": true})
	 */
	private $min=1;

	/**
	 * @var Integer $max is the maximum number of workers.
	 * @Column(type="smallint", nullable=false, options={"default": 1, "unsigned": true})
	 */
	private $max=1;

	/**
	 * @var Integer $increment is the number of workers that will be incremented by time.
	 * @Column(type="smallint", nullable=false, options={"default": 1, "unsigned": true})
	 */
	private $increment=1;

	/**
	 * @return Integer id
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return name
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param String $name
	 * @return WorkerCfg
	 */
	public function setName($name)
	{
		Validator::alnum('_')
			->noWhitespace()
			->notEmpty()
			->length(1, 255)
			->check($name);

		$this->name = $name;

		return $this;
	}

	/**
	 * @return Integer min
	 */
	public function getMin()
	{
		return $this->min;
	}

	/**
	 * @param Integer $min
	 * @return WorkerCfg
	 */
	public function setMin($min)
	{
		Validator::type('int')
			->between(0, 65535, true)
			->check($min);

		$this->min = $min;

		return $this;
	}

	/**
	 * @return Integer max
	 */
	public function getMax()
	{
		return $this->max;
	}

	/**
	 * @param Integer $max
	 * @return WorkerCfg
	 */
	public function setMax($max)
	{
		Validator::type('int')
			->between(1, 65535, true)
			->check($max);

		$this->max = $max;

		return $this;
	}

	/**
	 * @return Integer increment
	 */
	public function getIncrement()
	{
		return $this->increment;
	}

	/**
	 * @param Integer $increment
	 * @return WorkerCfg
	 */
	public function setIncrement($increment)
	{
		Validator::type('int')
			->between(1, 65535, true)
			->check($increment);

		$this->increment = $increment;

		return $this;
	}

}
