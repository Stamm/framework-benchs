<?php
namespace TYPO3\FLOW3\Security\Authorization;

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
 * An access decision voter manager
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope singleton
 */
class AccessDecisionVoterManager implements AccessDecisionManagerInterface {

	/**
	 * The object manager
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * The current security context
	 * @var TYPO3\FLOW3\Security\Context
	 */
	protected $securityContext;

	/**
	 * Array of \TYPO3\FLOW3\Security\Authorization\AccessDecisionVoterInterface objects
	 * @var array
	 */
	protected $accessDecisionVoters = array();

	/**
	 * If set to TRUE access will be granted for objects where all voters abstain from decision.
	 * @var boolean
	 */
	protected $allowAccessIfAllAbstain = FALSE;

	/**
	 * Constructor.
	 *
	 * @param \TYPO3\FLOW3\Object\ObjectManagerInterface $objectManager The object manager
	 * @param \TYPO3\FLOW3\Security\Context $securityContext The security context
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function __construct(\TYPO3\FLOW3\Object\ObjectManagerInterface $objectManager, \TYPO3\FLOW3\Security\Context $securityContext) {
		$this->objectManager = $objectManager;
		$this->securityContext = $securityContext;
	}

	/**
	 * Injects the configuration settings
	 *
	 * @param array $settings
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function injectSettings(array $settings) {
		$this->createAccessDecisionVoters($settings['security']['authorization']['accessDecisionVoters']);
		$this->allowAccessIfAllAbstain = $settings['security']['authorization']['allowAccessIfAllVotersAbstain'];
	}

	/**
	 * Returns the configured access decision voters
	 *
	 * @return array Array of \TYPO3\FLOW3\Security\Authorization\AccessDecisionVoterInterface objects
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getAccessDecisionVoters() {
		return $this->accessDecisionVoters();
	}

	/**
	 * Decides if access should be granted on the given object in the current security context.
	 * It iterates over all available \TYPO3\FLOW3\Security\Authorization\AccessDecisionVoterInterface objects.
	 * If all voters abstain, access will be denied by default, except $allowAccessIfAllAbstain is set to TRUE.
	 *
	 * @param \TYPO3\FLOW3\AOP\JoinPointInterface $joinPoint The joinpoint to decide on
	 * @return void
	 * @throws \TYPO3\FLOW3\Security\Exception\AccessDeniedException If access is not granted
	 */
	public function decideOnJoinPoint(\TYPO3\FLOW3\AOP\JoinPointInterface $joinPoint) {
		$denyVotes = 0;
		$grantVotes = 0;
		$abstainVotes = 0;

		foreach ($this->accessDecisionVoters as $voter) {
			$vote = $voter->voteForJoinPoint($this->securityContext, $joinPoint);
			switch ($vote) {
				case \TYPO3\FLOW3\Security\Authorization\AccessDecisionVoterInterface::VOTE_DENY:
					$denyVotes++;
					break;
				case \TYPO3\FLOW3\Security\Authorization\AccessDecisionVoterInterface::VOTE_GRANT:
					$grantVotes++;
					break;
				case \TYPO3\FLOW3\Security\Authorization\AccessDecisionVoterInterface::VOTE_ABSTAIN:
					$abstainVotes++;
					break;
			}
		}

		if ($denyVotes === 0 && $grantVotes > 0) {
			return;
		}
		if ($denyVotes === 0 && $grantVotes === 0 && $abstainVotes > 0 && $this->allowAccessIfAllAbstain === TRUE) {
			return;
		}

		$votes = sprintf('(%d denied, %d granted, %d abstained)', $denyVotes, $grantVotes, $abstainVotes);
		throw new \TYPO3\FLOW3\Security\Exception\AccessDeniedException('Access denied ' . $votes, 1222268609);
	}

	/**
	 * Decides if access should be granted on the given resource in the current security context.
	 * It iterates over all available \TYPO3\FLOW3\Security\Authorization\AccessDecisionVoterInterface objects.
	 * If all voters abstain, access will be denied by default, except $allowAccessIfAllAbstain is set to TRUE.
	 *
	 * @param string $resource The resource to decide on
	 * @return void
	 * @throws \TYPO3\FLOW3\Security\Exception\AccessDeniedException If access is not granted
	 */
	public function decideOnResource($resource) {
		$denyVotes = 0;
		$grantVotes = 0;
		$abstainVotes = 0;

		foreach ($this->accessDecisionVoters as $voter) {
			$vote = $voter->voteForResource($this->securityContext, $resource);
			switch ($vote) {
				case \TYPO3\FLOW3\Security\Authorization\AccessDecisionVoterInterface::VOTE_DENY:
					$denyVotes++;
					break;
				case \TYPO3\FLOW3\Security\Authorization\AccessDecisionVoterInterface::VOTE_GRANT:
					$grantVotes++;
					break;
				case \TYPO3\FLOW3\Security\Authorization\AccessDecisionVoterInterface::VOTE_ABSTAIN:
					$abstainVotes++;
					break;
			}
		}

		if ($denyVotes === 0 && $grantVotes > 0) {
			return;
		}
		if ($denyVotes === 0 && $grantVotes === 0 && $abstainVotes > 0 && $this->allowAccessIfAllAbstain === TRUE) {
			return;
		}

		$votes = sprintf('(%d denied, %d granted, %d abstained)', $denyVotes, $grantVotes, $abstainVotes);
		throw new \TYPO3\FLOW3\Security\Exception\AccessDeniedException('Access denied ' . $votes, 1283175927);
	}

	/**
	 * Creates and sets the configured access decision voters
	 *
	 * @param array $voterClassNames Array of access decision voter class names
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	protected function createAccessDecisionVoters(array $voterClassNames) {
		foreach ($voterClassNames as $voterClassName) {
			if (!$this->objectManager->isRegistered($voterClassName)) throw new \TYPO3\FLOW3\Security\Exception\VoterNotFoundException('No voter of type ' . $voterClassName . ' found!', 1222267934);

			$voter = $this->objectManager->get($voterClassName);
			if (!($voter instanceof \TYPO3\FLOW3\Security\Authorization\AccessDecisionVoterInterface)) throw new \TYPO3\FLOW3\Security\Exception\VoterNotFoundException('The found voter class did not implement \TYPO3\FLOW3\Security\Authorization\AccessDecisionVoterInterface', 1222268008);

			$this->accessDecisionVoters[] = $voter;
		}
	}
}

?>