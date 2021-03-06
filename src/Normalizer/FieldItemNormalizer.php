<?php

/**
 * @file
 * Contains \Drupal\metatag\Normalizer\FieldItemNormalizer.
 */

namespace Drupal\metatag\Normalizer;

use Drupal\Core\Field\FieldItemInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Drupal\serialization\Normalizer\NormalizerBase;

/**
 * Converts the Metatag field item object structure to METATAG array structure.
 */
class FieldItemNormalizer extends NormalizerBase {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = 'Drupal\metatag\Plugin\Field\FieldType\MetatagFieldItem';

  /**
   * Implements \Symfony\Component\Serializer\Normalizer\NormalizerInterface::normalize()
   */
  public function normalize($field_item, $format = NULL, array $context = []) {
    $values = $field_item->toArray();

    // Convert tokens if there are any.
    $token = \Drupal::token();
    $values['value'] = $token->replace($values['value']);

    if (isset($context['langcode'])) {
      $values['lang'] = $context['langcode'];
    }

    // Detect any data format that could be JSON (since we can define our own).
    if (strpos($format, 'json') !== FALSE) {
      $tags = [];
      // Get serialized value and break it into an array of tags with values.
      $serialized_value = $field_item->get('value')->getValue();
      $tags += unserialize($serialized_value);
    }

    // Convert tokens if there are any.
    $tags = array_map(function ($s) {
      $token = \Drupal::token();
      return $token->replace($s);
    }, $tags);

    // Mock the field array similar to the other fields.
    return array(
      $field_item->getfieldDefinition()->getName() => [
        [
          // Kept for backwards compatibility.
          'value' => $values['value'],
          'values' => $tags,
          'lang' => $values['lang']
        ]
      ]
    );
  }
}
