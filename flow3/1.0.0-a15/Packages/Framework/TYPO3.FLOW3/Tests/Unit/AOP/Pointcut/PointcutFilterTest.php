<?php
namespace TYPO3\FLOW3\Tests\Unit\AOP\Pointcut;

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
 * Testcase for the Pointcut Filter
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class PointcutFilterTest extends \TYPO3\FLOW3\Tests\UnitTestCase {


	/**
	 * @test
	 * @expectedException \TYPO3\FLOW3\AOP\Exception\UnknownPointcutException
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function matchesThrowsAnExceptionIfTheSpecifiedPointcutDoesNotExist() {
		$className = 'Foo';
		$methodName = 'bar';
		$methodDeclaringClassName = 'Baz';
		$pointcutQueryIdentifier = 42;

		$mockProxyClassBuilder = $this->getMock('TYPO3\FLOW3\AOP\Builder\ProxyClassBuilder', array('findPointcut'), array(), '', FALSE);
		$mockProxyClassBuilder->expects($this->once())->method('findPointcut')->with('Aspect', 'pointcut')->will($this->returnValue(FALSE));

		$pointcutFilter = new \TYPO3\FLOW3\AOP\Pointcut\PointcutFilter('Aspect', 'pointcut');
		$pointcutFilter->injectProxyClassBuilder($mockProxyClassBuilder);
		$pointcutFilter->matches($className, $methodName, $methodDeclaringClassName, $pointcutQueryIdentifier);
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function matchesTellsIfTheSpecifiedRegularExpressionMatchesTheGivenClassName() {
		$className = 'Foo';
		$methodName = 'bar';
		$methodDeclaringClassName = 'Baz';
		$pointcutQueryIdentifier = 42;

		$mockPointcut = $this->getMock('TYPO3\FLOW3\AOP\Pointcut\Pointcut', array('matches'), array(), '', FALSE);
		$mockPointcut->expects($this->once())->method('matches')->with($className, $methodName, $methodDeclaringClassName, $pointcutQueryIdentifier)->will($this->returnValue('the result'));

		$mockProxyClassBuilder = $this->getMock('TYPO3\FLOW3\AOP\Builder\ProxyClassBuilder', array('findPointcut'), array(), '', FALSE);
		$mockProxyClassBuilder->expects($this->once())->method('findPointcut')->with('Aspect', 'pointcut')->will($this->returnValue($mockPointcut));

		$pointcutFilter = new \TYPO3\FLOW3\AOP\Pointcut\PointcutFilter('Aspect', 'pointcut');
		$pointcutFilter->injectProxyClassBuilder($mockProxyClassBuilder);
		$this->assertSame('the result', $pointcutFilter->matches($className, $methodName, $methodDeclaringClassName, $pointcutQueryIdentifier));
	}

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getRuntimeEvaluationsDefinitionReturnsTheDefinitionArrayFromThePointcut() {
		$mockPointcut = $this->getMock('TYPO3\FLOW3\AOP\Pointcut\Pointcut', array(), array(), '', FALSE);
		$mockPointcut->expects($this->once())->method('getRuntimeEvaluationsDefinition')->will($this->returnValue(array('evaluations')));

		$mockProxyClassBuilder = $this->getMock('TYPO3\FLOW3\AOP\Builder\ProxyClassBuilder', array('findPointcut'), array(), '', FALSE);
		$mockProxyClassBuilder->expects($this->once())->method('findPointcut')->with('Aspect', 'pointcut')->will($this->returnValue($mockPointcut));

		$pointcutFilter = new \TYPO3\FLOW3\AOP\Pointcut\PointcutFilter('Aspect', 'pointcut');
		$pointcutFilter->injectProxyClassBuilder($mockProxyClassBuilder);
		$this->assertEquals(array('evaluations'), $pointcutFilter->getRuntimeEvaluationsDefinition(), 'Something different from an array was returned.');
	}

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getRuntimeEvaluationsDefinitionReturnsAnEmptyArrayIfThePointcutDoesNotExist() {
		$mockProxyClassBuilder = $this->getMock('TYPO3\FLOW3\AOP\Builder\ProxyClassBuilder', array('findPointcut'), array(), '', FALSE);
		$mockProxyClassBuilder->expects($this->once())->method('findPointcut')->with('Aspect', 'pointcut')->will($this->returnValue(FALSE));

		$pointcutFilter = new \TYPO3\FLOW3\AOP\Pointcut\PointcutFilter('Aspect', 'pointcut');
		$pointcutFilter->injectProxyClassBuilder($mockProxyClassBuilder);
		$this->assertEquals(array(), $pointcutFilter->getRuntimeEvaluationsDefinition(), 'The definition array has not been returned as exptected.');
	}
}
?>