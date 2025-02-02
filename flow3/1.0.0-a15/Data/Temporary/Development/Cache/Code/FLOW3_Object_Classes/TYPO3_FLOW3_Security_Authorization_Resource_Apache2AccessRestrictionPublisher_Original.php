<?php
namespace TYPO3\FLOW3\Security\Authorization\Resource;

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
 * An access restriction publisher that publishes .htaccess files to configure apache2 restrictions
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope singleton
 */
class Apache2AccessRestrictionPublisher_Original implements \TYPO3\FLOW3\Security\Authorization\Resource\AccessRestrictionPublisherInterface {

	/**
	 * @var \TYPO3\FLOW3\Utility\Environment
	 * @inject
	 */
	protected $environment;

	/**
	 * Publishes an Apache2 .htaccess file which allows access to the given directory only for the current session remote ip
	 *
	 * @param string $path The path to publish the restrictions for
	 * @return void
	 */
	public function publishAccessRestrictionsForPath($path) {
		$content = "Deny from all\nAllow from " . $this->environment->getRemoteAddress();

		file_put_contents($path . '.htaccess', $content);
	}
}


#0             %CLASS%TYPO3_FLOW3_Security_Authorization_Resource_Apache2AccessRestrictionPublisher2399      