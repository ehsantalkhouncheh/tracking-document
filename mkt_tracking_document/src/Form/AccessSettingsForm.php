<?php


namespace Drupal\mkt_tracking_document\Form;


use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure mkt_tracking_document settings for this site.
 */
class AccessSettingsForm extends ConfigFormBase
{

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'mkt_tracking_document_access_settings';
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames()
    {
        return ['mkt_tracking_document.access_settings'];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['customer_id'] = [
            '#type' => 'textarea',
            '#title' => $this->t("Valid Customer ID's"),
            '#required' => FALSE,
            '#default_value' => $this->config('mkt_tracking_document.access_settings')
                ->get('customer_id'),
        ];
        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        parent::validateForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $this->config('mkt_tracking_document.access_settings')
            ->set('customer_id', $form_state->getValue('customer_id'))
            ->save();
        parent::submitForm($form, $form_state);
    }

}
