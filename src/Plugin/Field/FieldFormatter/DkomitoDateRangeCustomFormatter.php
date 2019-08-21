<?php

namespace Drupal\dkomito_drupal_tools\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime\Plugin\Field\FieldFormatter\DateTimeCustomFormatter;
use Drupal\datetime_range\DateTimeRangeTrait;
use Drupal\datetime_range\Plugin\Field\FieldFormatter\DateRangeCustomFormatter;

/**
 * Plugin implementation of the 'dkomito_date_range_custom' formatter.
 *
 * @FieldFormatter(
 *   id = "dkomito_date_range_custom",
 *   label = @Translation("DKomito Custom"),
 *   field_types = {
 *     "daterange"
 *   }
 * )
 */
class DkomitoDateRangeCustomFormatter extends DateRangeCustomFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    // @todo Evaluate removing this method in
    // https://www.drupal.org/node/2793143 to determine if the behavior and
    // markup in the base class implementation can be used instead.
    $elements = [];
    $separator = $this->getSetting('separator');
		
		$all_day = ($items->getSetting('datetime_type') == 'allday');
	
    foreach ($items as $delta => $item) {
      if (!empty($item->start_date) && !empty($item->end_date)) {
        /** @var \Drupal\Core\Datetime\DrupalDateTime $start_date */
        $start_date = $item->start_date;
        /** @var \Drupal\Core\Datetime\DrupalDateTime $end_date */
        $end_date = $item->end_date;
				
				$start_timestamp = $start_date->getTimestamp();
				$end_timestamp = $end_date->getTimestamp();
				
		//	Get date only for an all day format
				if($all_day){
					$start_timestamp = $this->dateFormatter->format($start_timestamp, 'custom', 'Y-m-d');
					$end_timestamp = $this->dateFormatter->format($end_timestamp, 'custom', 'Y-m-d');
				}
				
        if ($start_timestamp !== $end_timestamp) {
          $elements[$delta] = [
            'start_date' => $this->buildDate($start_date),
            'separator' => ['#plain_text' => ' ' . $separator . ' '],
            'end_date' => $this->buildDate($end_date),
          ];
        }
        else {
          $elements[$delta] = $this->buildDate($start_date);
        }
      }
    }

    return $elements;
  }
}
