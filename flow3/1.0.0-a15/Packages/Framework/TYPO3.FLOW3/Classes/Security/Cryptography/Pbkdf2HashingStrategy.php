<?php
declare(ENCODING = 'utf-8');
namespace TYPO3\FLOW3\Security\Cryptography;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
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

require_once(FLOW3_PATH_FLOW3 . 'Resources/PHP/iSecurity/Security_Randomizer.php');

/**
 * A PBKDF2 based password hashing strategy
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser Public License, version 3 or later
 */
class Pbkdf2HashingStrategy implements \TYPO3\FLOW3\Security\Cryptography\PasswordHashingStrategyInterface {

	/**
	 * Length of the dynamic random salt to generate in bytes
	 * @var integer
	 */
	protected $dynamicSaltLength;

	/**
	 * Hash iteration count, high counts (>10.000) make brute-force attacks unfeasible
	 * @var integer
	 */
	protected $iterationCount;

	/**
	 * Derived key length
	 * @var integer
	 */
	protected $derivedKeyLength;

	/**
	 * Hash algorithm to use, see hash_algos()
	 * @var string
	 */
	protected $algorithm;

	/**
	 * Construct a PBKDF2 hashing strategy with the given parameters
	 *
	 * @param integer $dynamicSaltLength Length of the dynamic random salt to generate in bytes
	 * @param integer $iterationCount Hash iteration count, high counts (>10.000) make brute-force attacks unfeasible
	 * @param integer $derivedKeyLength Derived key length
	 * @param string $algorithm Hash algorithm to use, see hash_algos()
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function __construct($dynamicSaltLength, $iterationCount, $derivedKeyLength, $algorithm) {
		$this->dynamicSaltLength = $dynamicSaltLength;
		$this->iterationCount = $iterationCount;
		$this->derivedKeyLength = $derivedKeyLength;
		$this->algorithm = $algorithm;
	}

	/**
	 * Hash a password for storage using PBKDF2 and the configured parameters.
	 * Will use a combination of a random dynamic salt and the given static salt.
	 *
	 * @param string $password Cleartext password that should be hashed
	 * @param string $staticSalt Static salt that will be appended to the random dynamic salt
	 * @return string A Base64 encoded string with the derived key (hashed password) and dynamic salt
	 */
	public function hashPassword($password, $staticSalt = NULL) {
		$dynamicSalt = \Security_Randomizer::getRandomBytes($this->dynamicSaltLength);
		$result = \TYPO3\FLOW3\Security\Cryptography\Algorithms::pbkdf2($password, $dynamicSalt . $staticSalt, $this->iterationCount, $this->derivedKeyLength, $this->algorithm);
		return base64_encode($dynamicSalt) . ',' . base64_encode($result);
	}

	/**
	 * Validate a password against a derived key (hashed password) and salt using PBKDF2.
	 * Iteration count and algorithm have to match the parameters when generating the derived key.
	 *
	 * @param string $password The cleartext password
	 * @param string $hashedPasswordAndSalt The derived key and salt in Base64 encoding as returned by hashPassword for verification
	 * @param string $staticSalt Static salt that will be appended to the dynamic salt
	 * @return boolean TRUE if the given password matches the hashed password
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function validatePassword($password, $hashedPasswordAndSalt, $staticSalt = NULL) {
		$parts = explode(',', $hashedPasswordAndSalt);
		if (count($parts) !== 2) {
			throw new \InvalidArgumentException('The derived key with salt must contain a salt, separated with a comma from the derived key', 1306172911);
		}
		$dynamicSalt = base64_decode($parts[0]);
		$derivedKey = base64_decode($parts[1]);
		$derivedKeyLength = strlen($derivedKey);
		return $derivedKey === \TYPO3\FLOW3\Security\Cryptography\Algorithms::pbkdf2($password, $dynamicSalt . $staticSalt, $this->iterationCount, $derivedKeyLength, $this->algorithm);
	}

}
?>