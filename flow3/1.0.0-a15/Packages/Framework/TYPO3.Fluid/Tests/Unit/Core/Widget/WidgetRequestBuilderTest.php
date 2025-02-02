<?php
namespace TYPO3\Fluid\Tests\Unit\Core\Widget;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
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
 * Testcase for WidgetRequestBuilder
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class WidgetRequestBuilderTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * @var TYPO3\Fluid\Core\Widget\WidgetRequestBuilder
	 */
	protected $widgetRequestBuilder;

	/**
	 * @var TYPO3\FLOW3\Object\ObjectManagerInterface
	 */
	protected $mockObjectManager;

	/**
	 * @var TYPO3\Fluid\Core\Widget\WidgetRequest
	 */
	protected $mockWidgetRequest;

	/**
	 * @var TYPO3\Fluid\Core\Widget\AjaxWidgetContextHolder
	 */
	protected $mockAjaxWidgetContextHolder;

	/**
	 * @var TYPO3\Fluid\Core\Widget\WidgetContext
	 */
	protected $mockWidgetContext;

	/**
	 * @var TYPO3\FLOW3\Utility\Environment
	 */
	protected $mockEnvironment;

	/**
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setUp() {
		$this->widgetRequestBuilder = $this->getAccessibleMock('TYPO3\Fluid\Core\Widget\WidgetRequestBuilder', array('setArgumentsFromRawRequestData'));

		$this->mockWidgetRequest = $this->getMock('TYPO3\FLOW3\MVC\Web\Request');

		$this->mockObjectManager = $this->getMock('TYPO3\FLOW3\Object\ObjectManagerInterface');
		$this->mockObjectManager->expects($this->once())->method('create')->with('TYPO3\FLOW3\MVC\Web\Request')->will($this->returnValue($this->mockWidgetRequest));

		$this->widgetRequestBuilder->_set('objectManager', $this->mockObjectManager);

		$this->mockWidgetContext = $this->getMock('TYPO3\Fluid\Core\Widget\WidgetContext');

		$this->mockAjaxWidgetContextHolder = $this->getMock('TYPO3\Fluid\Core\Widget\AjaxWidgetContextHolder');
		$this->widgetRequestBuilder->injectAjaxWidgetContextHolder($this->mockAjaxWidgetContextHolder);
		$this->mockAjaxWidgetContextHolder->expects($this->once())->method('get')->will($this->returnValue($this->mockWidgetContext));

		$this->mockEnvironment = $this->getMock('TYPO3\FLOW3\Utility\Environment', array(), array(), '', FALSE);
		$this->mockEnvironment->expects($this->any())->method('getRequestUri')->will($this->returnValue(new \TYPO3\FLOW3\Property\DataType\Uri('http://request.uri.invalid/some/widget/request')));
		$this->mockEnvironment->expects($this->any())->method('getBaseUri')->will($this->returnValue(new \TYPO3\FLOW3\Property\DataType\Uri('http://request.uri.invalid/')));
		$this->widgetRequestBuilder->_set('environment', $this->mockEnvironment);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function buildSetsRequestMethodFromEnvironment() {
		$this->mockEnvironment->expects($this->once())->method('getRequestMethod')->will($this->returnValue('POST'));
		$this->mockWidgetRequest->expects($this->once())->method('setMethod')->with('POST');

		$this->widgetRequestBuilder->build();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function buildCallsSetArgumentsFromRawRequestData() {
		$this->widgetRequestBuilder->expects($this->once())->method('setArgumentsFromRawRequestData')->with($this->mockWidgetRequest);

		$this->widgetRequestBuilder->build();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function buildSetsControllerActionNameFromGetArguments() {
		$this->mockEnvironment->expects($this->once())->method('getRawGetArguments')->will($this->returnValue(array('action' => 'myaction', 'typo3-fluid-widget-id' => '')));
		$this->mockWidgetRequest->expects($this->once())->method('setControllerActionName')->with('myaction');

		$this->widgetRequestBuilder->build();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function buildSetsWidgetContext() {
		$this->mockEnvironment->expects($this->once())->method('getRawGetArguments')->will($this->returnValue(array('typo3-fluid-widget-id' => '123')));
		$this->mockAjaxWidgetContextHolder->expects($this->once())->method('get')->with('123')->will($this->returnValue($this->mockWidgetContext));
		$this->mockWidgetRequest->expects($this->once())->method('setArgument')->with('__widgetContext', $this->mockWidgetContext);

		$this->widgetRequestBuilder->build();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function buildReturnsRequest() {
		$expected = $this->mockWidgetRequest;
		$actual = $this->widgetRequestBuilder->build();
		$this->assertSame($expected, $actual);
	}
}
?>