<?php
/**
 * Part of Phoenix project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Phoenix\Form\Renderer;

use Phoenix\Script\BootstrapScript;
use Windwalker\Legacy\Core\Widget\WidgetHelper;
use Windwalker\Legacy\Dom\HtmlElement;
use Windwalker\Legacy\Form\Field\AbstractField;
use Windwalker\Legacy\Form\Field\HiddenField;
use Windwalker\Legacy\Form\Renderer\FormRendererInterface;
use function Windwalker\Legacy\h;
use Windwalker\Legacy\Utilities\Arr;

/**
 * The BootstrapRenderer class.
 *
 * @since  1.0.13
 * @deprecated Legacy code
 */
class InputRenderer implements FormRendererInterface
{
    /**
     * Property renderers.
     *
     * @var  callable[]
     */
    protected static $renderers = [];

    /**
     * Property aliases.
     *
     * @var  array
     */
    protected static $aliases = [
        'spacer' => 'default',
    ];

    /**
     * Property templatePrefix.
     *
     * @var  string
     */
    protected static $templatePrefix = 'phoenix.bootstrap.';

    /**
     * renderField
     *
     * @param AbstractField $field
     * @param array         $attribs
     * @param array         $options
     *
     * @return string
     */
    public function renderField(AbstractField $field, array $attribs = [], array $options = [])
    {
        $noLabel   = Arr::get($options, 'no_label', false);
        $hideLabel = Arr::get($options, 'hide_label', false);

        if ($hideLabel) {
            $field->appendAttribute('labelClass', 'sr-only');
        }

        return WidgetHelper::render('phoenix.bootstrap.field.control', [
            'field' => $field,
            'labelHtml' => $noLabel ? '' : $field->renderLabel($options),
            'inputHtml' => $field->renderInput($options),
            'attribs' => $attribs,
            'noLabel' => $noLabel,
            'hideLabel' => $hideLabel,
            'options' => $options,
        ], WidgetHelper::EDGE);
    }

    /**
     * renderLabel
     *
     * @param AbstractField $field
     * @param array         $attribs
     *
     * @param array         $options
     *
     * @return string
     */
    public function renderLabel(AbstractField $field, array $attribs = [], array $options = [])
    {
        if ($field instanceof HiddenField) {
            return '';
        }

        $attribs['class'] .= ' has-tooltip ' . $field->getAttribute(
            'labelWidth',
            Arr::get($options, 'vertical') ? 'col-md-12 col-12' : 'col-md-3'
        );

        $label = $field->getLabel();

        if ($field->get('required')) {
            $label .= '<span class="windwalker-input-required-hint"> *</span>';
        }

        if ($desc = $field->get('description')) {
            $label .= ' <span class="fa fa-question-circle">';
        }

        return (string) h('label', $attribs, $label);
    }

    /**
     * renderDefault
     *
     * @param   AbstractField $field
     * @param   array         $attribs
     * @param   array         $options
     *
     * @return string
     */
    public function renderInput(AbstractField $field, array $attribs = [], array $options = [])
    {
        $type = $field->getType();

        $type = static::resolveAlias($type);

        $handler = static::getRenderer($type);

        if ($handler) {
            return $handler($field, $attribs, $options);
        }

        $method = 'render' . ucfirst($type);

        if (is_callable([$this, $method])) {
            return $this->$method($field, $attribs, $options);
        }

        $attribs = Arr::def($attribs, 'class', '');

        $attribs['class'] .= ($field->get('readonly') || $field->get('disabled')) && $field->get('plain-text')
            && BootstrapScript::$currentVersion === 4
            ? ' form-control-plaintext'
            : ' form-control';

        return $field->buildInput($attribs);
    }

    /**
     * renderRadio
     *
     * @param AbstractField $field
     *
     * @return  string
     */
    public static function renderRadio(AbstractField $field, array $attribs = [])
    {
        $attribs          = Arr::def($attribs, 'class', '');
        $attribs['class'] .= ' radio-container input-list-container';

        return WidgetHelper::render(static::getTemplatePrefix() . 'field.radio', [
            'attribs' => $attribs,
            'field' => $field,
        ], WidgetHelper::EDGE);
    }

    /**
     * renderRadio
     *
     * @param AbstractField $field
     * @param array         $attribs
     *
     * @return string
     */
    public static function renderCheckboxes(AbstractField $field, array $attribs = [])
    {
        $attribs          = Arr::def($attribs, 'class', '');
        $attribs['class'] .= ' checkbox-container input-list-container form-check';

        return WidgetHelper::render(static::getTemplatePrefix() . 'field.checkboxes', [
            'attribs' => $attribs,
            'field' => $field,
        ], WidgetHelper::EDGE);
    }

    /**
     * renderRadio
     *
     * @param AbstractField $field
     * @param array         $attribs
     *
     * @return string
     */
    public static function renderSwitch(AbstractField $field, array $attribs = [])
    {
//        $attribs = Arr::def($attribs, 'class', '');
//        $attribs['class'] .= ' checkbox-container input-list-container';

        $attribs          = Arr::def($attribs, 'class', '');
        $attribs['class'] .= ' form-control';

        return WidgetHelper::render(static::getTemplatePrefix() . 'field.switch', [
            'attribs' => $attribs,
            'field' => $field,
        ], WidgetHelper::EDGE);
    }

    /**
     * renderSpacer
     *
     * @param AbstractField $field
     * @param array         $attribs
     *
     * @return string
     */
    public static function renderSpacer(AbstractField $field, array $attribs = [])
    {
        return WidgetHelper::render(static::getTemplatePrefix() . 'field.spacer', [
            'attribs' => $attribs,
            'field' => $field,
        ], WidgetHelper::EDGE);
    }

    /**
     * renderHidden
     *
     * @param AbstractField $field
     * @param array         $attribs
     *
     * @return string
     */
    protected static function renderHidden(AbstractField $field, array $attribs = [])
    {
        return $field->buildInput($attribs);
    }

    /**
     * resolveAlias
     *
     * @param   string $type
     *
     * @return  string
     */
    public static function resolveAlias($type)
    {
        if (isset(static::$aliases[$type])) {
            return static::$aliases[$type];
        }

        return $type;
    }

    /**
     * addAlias
     *
     * @param   string $type
     * @param   string $alias
     *
     * @return  void
     */
    public static function addAlias($type, $alias)
    {
        static::$aliases[$type] = $alias;
    }

    /**
     * Method to get property Aliases
     *
     * @return  array
     */
    public static function getAliases()
    {
        return static::$aliases;
    }

    /**
     * Method to set property aliases
     *
     * @param   array $aliases
     *
     * @return  void
     */
    public static function setAliases($aliases)
    {
        static::$aliases = $aliases;
    }

    /**
     * addRenderer
     *
     * @param   string   $type
     * @param   callable $renderer
     *
     * @return  void
     */
    public static function addRenderer($type, $renderer)
    {
        if (!is_callable($renderer)) {
            throw new \InvalidArgumentException($type . ' renderer should be callable.');
        }

        static::$renderers[$type] = $renderer;
    }

    /**
     * getRenderer
     *
     * @param   string $name
     *
     * @return  \callable
     */
    public static function getRenderer($name)
    {
        if (!isset(static::$renderers[$name])) {
            return null;
        }

        return static::$renderers[$name];
    }

    /**
     * Method to get property Renderers
     *
     * @return  \callable[]
     */
    public static function getRenderers()
    {
        return static::$renderers;
    }

    /**
     * Method to set property renderers
     *
     * @param   \callable[] $renderers
     *
     * @return  void
     */
    public static function setRenderers(array $renderers)
    {
        static::$renderers = $renderers;
    }

    /**
     * Method to get property TemplatePrefix
     *
     * @return  string
     */
    public static function getTemplatePrefix()
    {
        return static::$templatePrefix;
    }

    /**
     * Method to set property templatePrefix
     *
     * @param   string $templatePrefix
     *
     * @return  void
     */
    public static function setTemplatePrefix($templatePrefix)
    {
        static::$templatePrefix = $templatePrefix;
    }
}
