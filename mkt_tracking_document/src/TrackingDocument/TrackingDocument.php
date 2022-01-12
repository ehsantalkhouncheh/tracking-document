<?php

namespace Drupal\mkt_tracking_document\TrackingDocument;

use Drupal;
use Drupal\Core\Database\Database;
use Drupal\Core\Mail\Plugin\Mail\PhpMail;
use Drupal\mkt_log_manager\Logger\Logger;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;


class TrackingDocument
{
    /**
     * @param $cid
     *
     * @return bool|null
     */
    public static function checkCustomerId($cid): ?bool
    {

        $customerIds = Drupal::config('mkt_tracking_document.access_settings')
            ->get('customer_id');
        if (isset($customerIds) && !($customerIds == "")) {
            foreach (explode(',', $customerIds) as $customerId) {
                if ($customerId == $cid) {
                    return TRUE;
                } else {
                    continue;
                }
            }
            return FALSE;
        } else {
            return TRUE;
        }

    }

    /**
     * @param $customer_id
     * @param $time
     *
     * @return void
     */
    public static function bSendInternalEmailForPriceChangeVisit($customer_id, $time)
    {
        $send_mail = new PhpMail(); // this is used to send HTML emails
        $emails = Drupal::config('mkt_tracking_document.settings')
            ->get('email');
        foreach (explode(',', $emails) as $email) {
            $message['headers'] = [
                'content-type' => 'text/html; charset=UTF-8; format=flowed',
                'MIME-Version' => '1.0',
                'From' => 'info@yoursite.de <info@yoursite.de>',
                'return-path' => 'info@yoursite.de',
            ];
            $message['to'] = $email;
            $message['subject'] = "New Visit to the Price change explanation's PDF for " . Drupal::request()
                    ->getHost();
            $message['body'] = "The PDF has been viewed by RAV nr $customer_id at $time";

            $result = $send_mail->mail($message);
            if ($result === TRUE) {
                Logger::infoEvent('Email message have been send from info@yoursite.de to ' . $email);
            } else {
                Logger::errorEvent('There was a problem sending your Email message and it was not sent.from mail is info@yoursite.de and to email is ' . $email);
            }
        }

    }

    public static function saveVisitInfo($customer_id)
    {
        $db_cnn = Database::getConnection();
        if (is_numeric($customer_id)) {
            $rows = $db_cnn->query("SELECT * FROM {mkt_tracking_documents} where customer_id=$customer_id");
            $rows->allowRowCount = TRUE;

            if ($rows->rowCount() > 0) {
                $num_row = $db_cnn->update('mkt_tracking_documents')
                    ->fields([
                        'count' => $rows->rowCount() + 1,
                    ])
                    ->condition('customer_id', $customer_id, '=')
                    ->execute();
            } else {
                $num_row = $db_cnn->insert('mkt_tracking_documents')
                    ->fields([
                        'uid',
                        'customer_id',
                        'count',
                        'created',
                    ])
                    ->values([
                        Drupal::currentUser()->id(),
                        $customer_id,
                        1,
                        \Drupal::time()->getCurrentTime(),
                    ])
                    ->execute();
            }
            return $num_row;
        } else {
            Logger::errorEvent('customer id must be an integer');
            throw new AccessDeniedHttpException();
        }

    }

    public static function getS3BucketUrl(): string
    {
        $root_url = Drupal::config('s3fs.settings')->get('root_folder');
        $upload_url = Drupal::config('mkt_tracking_document.settings')
            ->get('upload_url');
        if (strpos($upload_url, 'public://') >= 0) {
            $upload_url = str_replace('public://', 's3fs-public/', $upload_url);
        } elseif (strpos($upload_url, 'private://') >= 0) {
            $upload_url = str_replace('private://', 's3fs-public/', $upload_url);
        } else {
            Logger::errorEvent('upload ulr is not valid' . $upload_url);
        }
        return 'https://s3.eu-central-1.amazonaws.com/' . getenv('S3BUCKETNAME') . '/' . $root_url . '/' . $upload_url . '/';
    }

}
