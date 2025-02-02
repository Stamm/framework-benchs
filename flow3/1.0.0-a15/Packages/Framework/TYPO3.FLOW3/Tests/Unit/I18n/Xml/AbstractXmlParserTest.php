<?php
namespace TYPO3\FLOW3\Tests\Unit\I18n\Xml;

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
 * Testcase for the AbstractXmlParser class
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class AbstractXmlParserTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function invokesDoParsingFromRootMethodForActualParsing() {
		$sampleXmlFilePath = __DIR__ . '/../Fixtures/MockCldrData.xml';

		$parser = $this->getAccessibleMock('TYPO3\FLOW3\I18n\Xml\AbstractXmlParser', array('doParsingFromRoot'));
		$parser->expects($this->once())->method('doParsingFromRoot');
		$parser->getParsedData($sampleXmlFilePath);
	}

	/**
	 * @test
	 * @expectedException \TYPO3\FLOW3\I18n\Xml\Exception\InvalidXmlFileException
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function throwsExceptionWhenBadFilenameGiven() {
		$mockFilenamePath = 'foo';

		$parser = $this->getAccessibleMock('TYPO3\FLOW3\I18n\Xml\AbstractXmlParser', array('doParsingFromRoot'));
		$parser->getParsedData($mockFilenamePath);
	}
}

?>