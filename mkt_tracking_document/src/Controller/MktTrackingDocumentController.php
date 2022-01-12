<?php

namespace Drupal\mkt_tracking_document\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Drupal\file\Entity\File;
use Drupal\mkt_tracking_document\TrackingDocument\TrackingDocument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Returns responses for mkt_tracking_document routes.
 */
class MktTrackingDocumentController extends ControllerBase
{

    /**
     * Builds the response.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function build()
    {
        $query_string = \Drupal::request()->query->get('q');
        $custom_id = substr($query_string, 0, -2);
        if (TrackingDocument::checkCustomerId($custom_id)) {
            $file_id = Drupal::config('mkt_tracking_document.settings')->get('pdf');
            if ($file_id) {
                $oNewFile = File::load(reset($file_id));
                $file_name = $oNewFile->getFilename();
            }

            $build['content'] = [
                '#markup' => Markup::create('<div>
                                                <iframe  class="pdf" webkitallowfullscreen="" mozallowfullscreen="" allowfullscreen="" frameborder="no" width="100%" height="1200px" src="/libraries/pdf.js/web/viewer.html?file=' . TrackingDocument::getS3BucketUrl() . $file_name . '" data-src="' . TrackingDocument::getS3BucketUrl() . $file_name . '"></iframe>
                                            </div>'
                ),
            ];
            /**
             * send notification email
             */
            TrackingDocument::bSendInternalEmailForPriceChangeVisit($custom_id, date("Y-m-d H:i:s"));
            TrackingDocument::saveVisitInfo($custom_id);
            return $build;
        } else {
            throw new AccessDeniedHttpException();
        }

    }

}
