<?php
namespace TYPO3\FLOW3\Security\Authentication;

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

/**
 * The authentication provider resolver. It resolves the class name of a authentication provider based on names.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope singleton
 */
class AuthenticationProviderResolver_Original {

	/**
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * Constructor.
	 *
	 * @param \TYPO3\FLOW3\Object\ObjectManagerInterface $objectManager The object manager
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function __construct(\TYPO3\FLOW3\Object\ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Resolves the class name of an authentication provider. If a valid provider class name is given, it is just returned.
	 *
	 * @param string $providerName The (short) name of the provider
	 * @return string The object name of the authentication provider
	 * @throws \TYPO3\FLOW3\Security\Exception\NoAuthenticationProviderFoundException
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function resolveProviderClass($providerName) {
		$resolvedObjectName = $this->objectManager->getCaseSensitiveObjectName($providerName);
		if ($resolvedObjectName !== FALSE) return $resolvedObjectName;

		$resolvedObjectName = $this->objectManager->getCaseSensitiveObjectName('TYPO3\FLOW3\Security\Authentication\Provider\\' . $providerName);
		if ($resolvedObjectName !== FALSE) return $resolvedObjectName;

		throw new \TYPO3\FLOW3\Security\Exception\NoAuthenticationProviderFoundException('An authentication provider with the name "' . $providerName . '" could not be resolved.', 1217154134);
	}
}

#0             %CLASS%TYPO3_FLOW3_Security_Authentication_AuthenticationProviderResolver3285      