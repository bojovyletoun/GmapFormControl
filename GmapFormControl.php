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
    
    const LATITUDE = 'latitude';
    const LONGITUDE = 'longitude';

    /** @var array  default map options */
    private $options = array(
        'width' => 300,
        'height' => 300,
        'center' => array(
            self::LATITUDE => 0,
            self::LONGITUDE => 0,
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
        $latitude->name .= '['.self::LATITUDE.']';
        $latitude->id = $id . '-'.self::LATITUDE;
        $latitude->value = $this->value[self::LATITUDE];

        /* create longitude input */
        $longitude = clone $original;
        $longitude->name .= '['.self::LONGITUDE.']';
        $longitude->id = $id . '-'.self::LONGITUDE;
        $longitude->value = $this->value[self::LONGITUDE];

        if ($this->getValue() === NULL) {
            if (!isset($this->options['center'][self::LATITUDE])) { // allows simpler central point array
                $center = array(
                    self::LATITUDE => $this->options['center'][0],
                    self::LONGITUDE => $this->options['center'][1],
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