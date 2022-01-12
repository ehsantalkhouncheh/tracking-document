<?php

namespace Drupal\mkt_tracking_document\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;


/**
 * Configure mkt_tracking_document settings for this site.
 */
class SettingsForm extends ConfigFormBase
{

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'mkt_tracking_document_settings';
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames()
    {
        return ['mkt_tracking_document.settings'];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['upload_url'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Upload Url'),
            '#required' => TRUE,
            '#default_value' => $this->config('mkt_tracking_document.settings')
                ->get('upload_url'),
        ];
        $form['pdf'] = [
            '#type' => 'managed_file',
            '#title' => t('PDF Document'),
            '#required' => TRUE,
            '#upload_location' => $this->config('mkt_tracking_document.settings')
                ->get('upload_url'),
            '#multiple' => TRUE,
            '#default_value' => $this->config('mkt_tracking_document.settings')
                ->get('pdf'),
            '#upload_validators' => [
                'file_validate_extensions' => ['pdf'],
            ],

        ];
        $form['email'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Email Addresses'),
            '#required' => TRUE,
            '#default_value' => $this->config('mkt_tracking_document.settings')
                ->get('email'),
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
     * @throws \Drupal\Core\Entity\EntityStorageException
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        /* Fetch the array of the file stored temporarily in database */
        $pdf = $form_state->getValue('pdf');
        $file = File::load($pdf[0]);
        /* Set the status flag permanent of the file object */
        $file->setPermanent();
        /* Save the file in database */
        $file->save();
        $this->config('mkt_tracking_document.settings')
            ->set('upload_url', $form_state->getValue('upload_url'))
            ->set('pdf', $form_state->getValue('pdf'))
            ->set('email', $form_state->getValue('email'))
            ->save();
        parent::submitForm($form, $form_state);
    }

}
