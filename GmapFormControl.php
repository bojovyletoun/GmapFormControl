<?php

use Nette\Utils\Html,
    Nette\Utils\Strings,
    \Nette\Templating\FileTemplate;

/**
 * GmapFormControl
 * @author Jakub Jarabica (http://www.jam3son.sk)
 * 
 * @property-read string $template
 * 
 */
final class GmapFormControl extends Nette\Forms\Controls\BaseControl {

    /** @var array  default map options */
    private $options = array(
        'width' => 300,
        'height' => 300,
        'center' => array(
            'latitude' => 0,
            'longitude' => 0,
        ),
        'zoom' => 2,
    );
    /** @var FileTemplate */
    private $template;

    /**
     * Form container extension method. Do not call directly.
     * 	 
     * @param Container $form
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

        $this->template = dirname(__FILE__) . '/template.latte';
    }

    public function setTemplate($template) {
        $this->template = $template;
    }

    public function getValue() {
        return is_array($this->value) ? $this->value : NULL;
    }

    public function getControl() {
        $original = parent::getControl();
        $id = $original->id;

        /* create latitude input */
        $latitude = clone $original;
        $latitude->name .= '[latitude]';
        $latitude->id = $id . '-latitude';
        $latitude->value = $this->value['latitude'];

        /* create longitude input */
        $longitude = clone $original;
        $longitude->name .= '[longitude]';
        $longitude->id = $id . '-longitude';
        $longitude->value = $this->value['longitude'];

        if ($this->getValue() === NULL) {
            if (!isset($this->options['center']['latitude'])) { // allows simpler central point array
                $center = array(
                    'latitude' => $this->options['center'][0],
                    'longitude' => $this->options['center'][1],
                );
            } else {
                $center = $this->options['center'];
            }
        } else {
            $center = $this->getValue();
        }

        $template = new FileTemplate($this->template);
        $template->registerFilter(new Nette\Latte\Engine);

        $template->latitude = $latitude;
        $template->longitude = $longitude;
        $template->options = $this->options;
        $template->center = $center;
        $template->control_id = $id;

        return $template;
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