<?php

use Nette\Utils\Html,
 Nette\Utils\Strings;

/**
 * GmapFormControl
 * @author Jakub Jarabica (http://www.jam3son.sk)
 */
final class GmapFormControl extends Nette\Forms\Controls\BaseControl {

    /** @var Html  separator element template */
    protected $separator;
    /** @var Html  container element template */
    protected $container;
    /** @var array */
    private $coords = array('latitude', 'longitude');
    /** @var array  default map options */
    private $options = array(
        'width' => 300,
        'height' => 300,
        'center' => array(0, 0),
        'zoom' => 2,
    );

    /**
     * Form container extension method. Do not call directly.
     * 	 
     * @param FormContainer $form
     * @param string $name
     * @param string $label	
     * @param array $options 
     * @return GmapFormControl
     */
    public static function addGmapFormControl(Nette\Forms\Container $form, $name, $label, $options = NULL) {
        return $form[$name] = new self($label, $options);
    }

    /**
     * @param string $label
     * @param array $options
     */
    public function __construct($label, $options = NULL) {
        parent::__construct($label);
        if ($options !== NULL) {
            $this->options = array_merge($this->options, $options);
        }
        $this->container = Html::el('div');
        $this->separator = Html::el('br');
    }

    public function getValue() {
        return is_array($this->value) ? $this->value : NULL;
    }

    public function getSeparatorPrototype() {
        return $this->separator;
    }

    public function getContainerPrototype() {
        throw new LogicException('Can\'t return container prototype!');
    }

    public function getControl() {
        $original = parent::getControl();
        $container = $this->container;
        $separator = $this->separator;
        $id = $original->id;
        $container->id = "container-" . $id;
        $values = $this->value === NULL ? NULL : (array) $this->getValue();
        $label = /* Nette\Web\ */Html::el('label');

        foreach ($this->coords as $coord) {
            $control = clone $original;
            $control->name .= '[' . $coord . ']';
            $control->id = $label->for = $id . '-' . $coord;
            $control->value = $this->value[$coord];
            $label->setText($this->translate($coord));
            
            $container->add((string) $label . (string) $control . $separator);
        }

        if ($this->getValue() === NULL) {
            $center = $this->options['center'];
        } else {
            $center = $this->getValue();
        }

        $js = Html::el('script')->type("text/javascript")->setText('$(function() {
            var $id = "' . $id . '";
            var map_center = new google.maps.LatLng(' . $this->options['center'][0] . ', ' . $this->options['center'][1] . ');
            var $container = $("#container-"+$id).css("width", "' . $this->options['width'] . '").css("height", "' . $this->options['height'] . '");
            var $form = $container.closest("form");
            var $lat = $("#"+$id+"-latitude").hide();
            var $long = $("#"+$id+"-longitude").hide();
            $container.find("label").hide();
            $form.append($lat);
            $form.append($long);
            
    var myOptions = {
      zoom: ' . $this->options['zoom'] . ',
      center: map_center,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    var map = new google.maps.Map(document.getElementById("container-frmmapForm-mapa"),
        myOptions);
        var $current_marker = new google.maps.Marker({
      position: map_center,
      map: map,
      draggable: true
                });
                google.maps.event.addListener($current_marker, \'dragend\', function(event) {
        $lat.val(event.latLng.Da);
        $long.val(event.latLng.Ea);
        });
        google.maps.event.addListener(map, \'click\', function(event) {
        if($current_marker) {
            $current_marker.setMap(null);
        }
        $lat.val(event.latLng.Da);
        $long.val(event.latLng.Ea);
      $current_marker = new google.maps.Marker({
      position: event.latLng,
      map: map,
      draggable: true
                });
                google.maps.event.addListener($current_marker, \'dragend\', function(event) {
        $lat.val(event.latLng.Da);
        $long.val(event.latLng.Ea);
        });
            });
        })');
        $container->add($js);

        return $container;
    }

    /**
     * Generates label's HTML element.
     * 	 
     * @return Html
     */
    public function getLabel($caption = NULL) {
        $label = parent::getLabel($caption);
        $label->for = NULL;
        return $label;
    }

}