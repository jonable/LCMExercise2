<?php

namespace Drupal\exercise2\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * I chose to use the exercise2_node_view_alter hook for this exercise,
 * and including this an extra example of how to solve the premium content problem.
*/

 /**
 * Plugin implementation of the 'exercise2_premium_text' formatter.
 * Adds functionality to check if field_is_premium is set 
 * and user has correct permissions to view the Entities field.
 * 
 * @FieldFormatter(
 *   id = "exercise2_premium_text",
 *   module = "exercise2",
 *   label = @Translation("Premium text formatter"),
 *   field_types = {
 *     "text_with_summary"
 *   }
 * )
 */

class PremiumTextFormatter extends \Drupal\text\Plugin\Field\FieldFormatter\TextDefaultFormatter {
  
  protected $premium_field_name = 'field_is_premium';

  protected $role_name = 'premium';

  protected $view_permission_name = 'view full premium content';

 /**
  * Check if node is premium
  *
  * @param Drupal\Core\Entity\EntityInterface $entity
  * @return boolean
  */ 
  protected function isPremium(Drupal\Core\Entity\EntityInterface $entity){
    return ($entity->hasField('field_is_premium') && $entity->field_is_premium->value == true);
  }

  /**
   * Check if current user can view
   *
   * @param \Drupal\user\Entity\User $user
   * @return boolean
   */
  protected function userHasAccess(\Drupal\user\Entity\User $user){
    return ($user->hasRole($this->$role_name) || $user->hasPermission($this->$view_permission_name));
  }
  /**
   * Build the Paywall notice to be displayed to user.
   *
   * @param [array] $elements
   * @return [array] $build
   */
  private function buildPaywallNotice(&$elements){
      $config = \Drupal::config('exercise2.settings');            
      
      $url = "";
      $paywall_notice = t($config->get("paywall_notice"));
      $markup = NULL;
      // create markup for paywall notice and link.
      if(! empty($config->get('paywall_link'))){
          $url = $config->get('paywall_link');
          $url = Drupal\Core\Url::fromUri($url);
          // $url = Drupal\Core\Url::fromUri('user.login');
          $markup = Drupal\Core\Link::fromTextAndUrl($paywall_notice, $url);
          $markup = $markup->toRenderable();
      }else{
          $markup = [
              '#prefix'=>'<strong>',
              '#suffix'=>'</strong>',
              '#markup'=>$paywall_notice 
          ];                
      }
      array_push($build['body'], $markup);  
  }
  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    // This feels like too much heavy lifting for a field formatter...

    $parent_node = $items->getEntity();
    
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    // If field is marked premium and user does not have access, show summary + reg button
    // else show full content.
    $paywall_content = false;
    
    if($this->isPremium($parent_node) && $this->userHasAccess($user)){
      $paywall_content = true;
    }
      
      foreach ($items as $delta => $item) {
        // set #text to either node body or summar based on above.
        if($paywall_content){
          $text = $item->summary;
        }else{
          $text = $item->body;
        }
        // build the field render array.
        $elements[$delta] = [
          '#type' => 'processed_text',
          '#text' => $text,
          '#format' => $item->format,
          '#langcode' => $item
            ->getLangcode(),
        ];
      }
      // add a register button and notice if content is paywalled.
      if($paywall_content){
        $this->buildPaywallNotice($elements);
        // $url = Url::fromRoute('user.register');
        // $link = Link::fromTextAndUrl($this->t('Register'), $url);
        // array_push($elements, $link->toRenderable());
      }

    return $elements;
  }

}
