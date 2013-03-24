<?php

/*
 * This file is part of the Ivory Google Map package.
 *
 * (c) Eric GELOEN <geloen.eric@gmail.com>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Ivory\Tests\GoogleMap\Helper\Overlays;

use Ivory\GoogleMap\Overlays\Animation;
use Ivory\GoogleMap\Overlays\InfoWindow;
use Ivory\GoogleMap\Overlays\Marker;
use Ivory\GoogleMap\Helper\Overlays\MarkerHelper;

/**
 * Marker helper test.
 *
 * @author GeLo <geloen.eric@gmail.com>
 */
class MarkerHelperTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Ivory\GoogleMap\Helper\Overlays\MarkerHelper */
    protected $markerHelper;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->markerHelper = new MarkerHelper();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        unset($this->markerHelper);
    }

    public function testDefaultState()
    {
        $this->assertInstanceOf(
            'Ivory\GoogleMap\Helper\Base\CoordinateHelper',
            $this->markerHelper->getCoordinateHelper()
        );

        $this->assertInstanceOf(
            'Ivory\GoogleMap\Helper\Overlays\AnimationHelper',
            $this->markerHelper->getAnimationHelper()
        );

        $this->assertInstanceOf(
            'Ivory\GoogleMap\Helper\Overlays\InfoWindowHelper',
            $this->markerHelper->getInfoWindowHelper()
        );

        $this->assertInstanceOf(
            'Ivory\GoogleMap\Helper\Overlays\MarkerImageHelper',
            $this->markerHelper->getMarkerImageHelper()
        );

        $this->assertInstanceOf(
            'Ivory\GoogleMap\Helper\Overlays\MarkerShapeHelper',
            $this->markerHelper->getMarkerShapeHelper()
        );
    }

    public function testInitialState()
    {
        $coordinateHelper = $this->getMock('Ivory\GoogleMap\Helper\Base\CoordinateHelper');
        $animationHelper = $this->getMock('Ivory\GoogleMap\Helper\Overlays\AnimationHelper');
        $infoWindowHelper = $this->getMock('Ivory\GoogleMap\Helper\Overlays\InfoWindowHelper');
        $markerImageHelper = $this->getMock('Ivory\GoogleMap\Helper\Overlays\MarkerImageHelper');
        $markerShapeHelper = $this->getMock('Ivory\GoogleMap\Helper\Overlays\MarkerShapeHelper');

        $this->markerHelper = new MarkerHelper(
            $coordinateHelper,
            $animationHelper,
            $infoWindowHelper,
            $markerImageHelper,
            $markerShapeHelper
        );

        $this->assertSame($coordinateHelper, $this->markerHelper->getCoordinateHelper());
        $this->assertSame($animationHelper, $this->markerHelper->getAnimationHelper());
        $this->assertSame($infoWindowHelper, $this->markerHelper->getInfoWindowHelper());
        $this->assertSame($markerImageHelper, $this->markerHelper->getMarkerImageHelper());
        $this->assertSame($markerShapeHelper, $this->markerHelper->getMarkerShapeHelper());
    }

    public function testRenderWithoutOptions()
    {
        $map = $this->getMock('Ivory\GoogleMap\Map');
        $map
            ->expects($this->once())
            ->method('getJavascriptVariable')
            ->will($this->returnValue('map'));

        $marker = new Marker();
        $marker->setJavascriptVariable('marker');
        $marker->setPosition(1.1, 2.1, true);
        $marker->setAnimation(Animation::BOUNCE);

        $marker->setIcon('url');
        $marker->getIcon()->setJavascriptVariable('icon');

        $marker->setShadow('url');
        $marker->getShadow()->setJavascriptVariable('shadow');

        $marker->setShape('poly', array(1, 2, 3, 4));
        $marker->getShape()->setJavascriptVariable('shape');

        $marker->setInfoWindow(new InfoWindow('content'));
        $marker->getInfoWindow()->setJavascriptVariable('infoWindow');

        $expectedMarker = 'var marker = new google.maps.Marker({'.
            '"map":map,'.
            '"position":new google.maps.LatLng(1.1, 2.1, true), '.
            '"animation":google.maps.Animation.BOUNCE, '.
            '"icon":icon, '.
            '"shadow":shadow, '.
            '"shape":shape'.
            '});';

        $expected = <<<EOF
var icon = new google.maps.MarkerImage("url");
var shadow = new google.maps.MarkerImage("url");
var shape = new google.maps.MarkerShape({"type":"poly","coords":[1,2,3,4]});
$expectedMarker
var infoWindow = new google.maps.InfoWindow({"content":"content"});

EOF;

        $this->assertSame($expected, $this->markerHelper->render($marker, $map));
    }

    public function testRenderWithOptions()
    {
        $map = $this->getMock('Ivory\GoogleMap\Map');
        $map
            ->expects($this->any())
            ->method('getJavascriptVariable')
            ->will($this->returnValue('map'));

        $marker = new Marker();
        $marker->setJavascriptVariable('marker');
        $marker->setPosition(1.1, 2.1, true);
        $marker->setAnimation(Animation::BOUNCE);

        $marker->setIcon('url');
        $marker->getIcon()->setJavascriptVariable('icon');

        $marker->setShadow('url');
        $marker->getShadow()->setJavascriptVariable('shadow');

        $marker->setShape('poly', array(1, 2, 3, 4));
        $marker->getShape()->setJavascriptVariable('shape');

        $marker->setInfoWindow(new InfoWindow('content'));
        $marker->getInfoWindow()->setJavascriptVariable('infoWindow');
        $marker->getInfoWindow()->setOpen(true);

        $marker->setOptions(array('option1' => 'value1', 'option2' => 'value2'));

        $expectedMarker = 'var marker = new google.maps.Marker({'.
            '"map":map,'.
            '"position":new google.maps.LatLng(1.1, 2.1, true), '.
            '"animation":google.maps.Animation.BOUNCE, '.
            '"icon":icon, '.
            '"shadow":shadow, '.
            '"shape":shape,'.
            '"option1":"value1",'.
            '"option2":"value2"'.
            '});';

        $expected = <<<EOF
var icon = new google.maps.MarkerImage("url");
var shadow = new google.maps.MarkerImage("url");
var shape = new google.maps.MarkerShape({"type":"poly","coords":[1,2,3,4]});
$expectedMarker
var infoWindow = new google.maps.InfoWindow({"content":"content"});
infoWindow.open(map, marker);

EOF;

        $this->assertSame($expected, $this->markerHelper->render($marker, $map));
    }
}
