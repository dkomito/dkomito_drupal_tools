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
  public static function defaultSettings() {
    return [
      'intersect_dates' => FALSE,
    ] + parent::defaultSettings();
  }

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
					$separator = ' ' . $separator . ' ';
					$start_str = $this->buildDate($start_date);
					$end_str = $this->buildDate($end_date);
					
				//	Intersect the dates
					if($this->getSetting('intersect_dates')){
						$start_words = explode(' ', $start_str['#markup']);
						$end_words = explode(' ', $end_str['#markup']);
						foreach($start_words as $key => $value){
							if($value != $end_words[$key]){
								break;
							}
						}
						if($key > 0){
							$start_words = array_slice($start_words, 0, $key + 1);
							$end_words = array_slice($end_words, $key);
							$start_str['#markup'] = implode(' ', $start_words);
							$end_str['#markup'] = implode(' ', $end_words);
							$separator = trim($separator);
						}
					//	The start is the same, try to insect the year (only if the format ends with a year)
						else if(strtolower(substr(trim($this->getSetting('date_format')), -1)) == 'y'){
							for($key = count($start_words) - 1; $key >= 0; $key-- ){
								if($start_words[$key] != $end_words[$key]){
									break;
								}
							}								
							if($key < count($start_words) - 1){
								$start_words = array_slice($start_words, 0, $key + 1);
								$start_str['#markup'] = implode(' ', $start_words);
								$end_str['#markup'] = implode(' ', $end_words);
							}
						}
					}
					
          $elements[$delta] = [
            'start_date' => $start_str,
            'separator' => ['#plain_text' => $separator],
            'end_date' => $end_str,
          ];
        }
        else {
          $elements[$delta] = $this->buildDate($start_date);
        }
      }
    }

    return $elements;
  }
	
/**
 * {@inheritdoc}
 */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
		
		$form['intersect_dates'] = [
			'#title' => $this->t('Intersect Start and End Dates?'),
			'#type' => 'checkbox',
			'#default_value' => $this->getSetting('intersect_dates'),
			'#description' => $this->t('Where "Oct. 3 2019 - Oct. 5 2019" becomes "Oct. 3-5 2019".  Works best for language-based formats. (ie. "10/3/2019 - 10/5/2019" would be "10/3-5/2019")'),
		];
	
		return $form;
	}

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    if ($intersect_dates = $this->getSetting('intersect_dates')) {
      $summary[] = $this->t('Instersect Dates: %intersect_dates', ['%intersect_dates' => $intersect_dates]);
    }

    return $summary;
  }

}
