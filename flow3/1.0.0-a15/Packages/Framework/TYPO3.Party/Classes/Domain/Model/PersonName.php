<?php
namespace TYPO3\Party\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "Party".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * A person name
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 * @entity
 */
class PersonName {

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $firstName;

	/**
	 * @var string
	 */
	protected $middleName;

	/**
	 * @var string
	 */
	protected $lastName;

	/**
	 * @var string
	 */
	protected $otherName;

	/**
	 * @var string
	 */
	protected $alias;

	/**
	 * @var string
	 */
	protected $fullName;

	/**
	 * Constructs this person name
	 *
	 * @param string $title the title, e.g. "Mr." or "Mr. Phd"
	 * @param string $firstName the first name
	 * @param string $middleName the middle name
	 * @param string $lastName the last name
	 * @param string $otherName the "other" name, e.g. "IV." or "jr."
	 * @param string $alias an alias or nickname
	 * @author Robert Lemke <robert@typo3.org>
	 * @var string
	 */
	public function __construct($title = '', $firstName = '', $middleName = '', $lastName = '', $otherName = '', $alias = '') {
		$this->title = $title;
		$this->firstName = $firstName;
		$this->middleName = $middleName;
		$this->lastName = $lastName;
		$this->otherName = $otherName;
		$this->alias = $alias;

		$this->generateFullName();
	}

	/**
	 * @return void
	 */
	protected function generateFullName() {
		$nameParts = array(
			$this->title,
			$this->firstName,
			$this->middleName,
			$this->lastName,
			$this->otherName
		);
		$nameParts = array_map('trim', $nameParts);
		$filledNameParts = array();
		foreach($nameParts as $namePart) {
			if($namePart !== '') {
				$filledNameParts[] = $namePart;
			}
		}
		$this->fullName = implode(' ', $filledNameParts);
	}

	/**
	 * Setter for firstName
	 *
	 * @param string $firstName
	 * @return void
	 */
	public function setFirstName($firstName) {
		$this->firstName = $firstName;
		$this->generateFullName();
	}

	/**
	 * Setter for middleName
	 *
	 * @param string $middleName
	 * @return void
	 */
	public function setMiddleName($middleName) {
		$this->middleName = $middleName;
		$this->generateFullName();
	}

	/**
	 * Setter for lastName
	 *
	 * @param string $lastName
	 * @return void
	 */
	public function setLastName($lastName) {
		$this->lastName = $lastName;
		$this->generateFullName();
	}

	/**
	 * Setter for title
	 *
	 * @param string $title
	 * @return void
	 */
	public function setTitle($title) {
		$this->title = $title;
		$this->generateFullName();
	}

	/**
	 * Setter for otherName
	 *
	 * @param string $otherName
	 * @return void
	 */
	public function setOtherName($otherName) {
		$this->otherName = $otherName;
		$this->generateFullName();
	}

	/**
	 * Setter for alias
	 *
	 * @param string $alias
	 * @return void
	 */
	public function setAlias($alias) {
		$this->alias = $alias;
	}

	/**
	 * Getter for firstName
	 *
	 * @return string
	 */
	public function getFirstName() {
		return $this->firstName;
	}

	/**
	 * Getter for middleName
	 *
	 * @return string
	 */
	public function getMiddleName() {
		return $this->middleName;
	}

	/**
	 * Getter for lastName
	 *
	 * @return string
	 */
	public function getLastName() {
		return $this->lastName;
	}

	/**
	 * Getter for title
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Getter for otherName
	 *
	 * @return string
	 */
	public function getOtherName() {
		return $this->otherName;
	}

	/**
	 * Getter for alias
	 *
	 * @return string
	 */
	public function getAlias() {
		return $this->alias;
	}

	/**
	 * Returns the full name, e.g. "Mr. PhD John W. Doe"
	 *
	 * @return string The full person name
	 */
	public function getFullName() {
		return $this->fullName;
	}

	/**
	 * An alias for getFullName()
	 *
	 * @return string The full person name
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function  __toString() {
		return $this->fullName;
	}
}
?>