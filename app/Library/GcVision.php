<?php
/**
 * Created by PhpStorm.
 * User: leon.okubo
 * Date: 30/10/2018
 * Time: 20:37
 */

namespace App\Library;

use Google\Cloud\Vision\V1\ImageAnnotatorClient;

class GcVision
{
    protected $ext = '', $imageAnnotator;

    public function __construct()
    {

    }

    protected function imagecreate($image)
    {
        $imageCreateFunc = [
            'png' => 'imagecreatefrompng',
            'gd' => 'imagecreatefromgd',
            'gif' => 'imagecreatefromgif',
            'jpg' => 'imagecreatefromjpeg',
            'jpeg' => 'imagecreatefromjpeg',
        ];

        $this->ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        if (!array_key_exists($this->ext, $imageCreateFunc)) {
            throw new \Exception('Unsupported image extension');
        }

        return call_user_func($imageCreateFunc[$this->ext], $image);
    }

    protected function saveImagejpeg($image, $path)
    {
        $imageWriteFunc = [
            'png' => 'imagepng',
            'gd' => 'imagegd',
            'gif' => 'imagegif',
            'jpg' => 'imagejpeg',
            'jpeg' => 'imagejpeg',
        ];

        return call_user_func($imageWriteFunc[$this->ext], $image, $path);
    }

    public function detect_face_gcs($path)
    {
        $imageAnnotator = new ImageAnnotatorClient();

        # annotate the image
        $response = $imageAnnotator->faceDetection(file_get_contents($path));
        $faces = $response->getFaceAnnotations();

        $likelihoodName = ['DESCONHECIDO', 'MUITO_DIFICIL', 'DIFICIL', 'POSSIVEL', 'PROVAVEL', 'MUITO_PROVAVEL'];

        $imageArr['faces']['total'] = count($faces);
        $i = 0;
        foreach ($faces as $face) {
            $vertices = $face->getBoundingPoly()->getVertices();
            $foto = $this->imagecreate($path);
            if ($vertices) {
                $x1 = $vertices[0]->getX();
                $y1 = $vertices[0]->getY();
                $x2 = $vertices[2]->getX();
                $y2 = $vertices[2]->getY();
                imagerectangle($foto, $x1, $y1, $x2, $y2, 0x00ff00);
                $this->saveImagejpeg($foto, $path);
            }

            $bounds = [];
            foreach ($vertices as $vertex) {
                $bounds[] = sprintf('(%d,%d)', $vertex->getX(), $vertex->getY());
            }

            $imageArr['faces'][$i]['getAngerLikelihood'] = $likelihoodName[$face->getAngerLikelihood()];
            $imageArr['faces'][$i]['getJoyLikelihood'] = $likelihoodName[$face->getJoyLikelihood()];
            $imageArr['faces'][$i]['getSurpriseLikelihood'] = $likelihoodName[$face->getSurpriseLikelihood()];
            $imageArr['faces'][$i]['getDetectionConfidence'] = $likelihoodName[$face->getDetectionConfidence()];
            $imageArr['faces'][$i]['getHeadwearLikelihood'] = $likelihoodName[$face->getHeadwearLikelihood()];
            $imageArr['faces'][$i]['Bounds'] = join(', ',$bounds);
            $i++;
        }

        $imageAnnotator->close();
        return $imageArr;
    }

    public function objectLocalization($path){
        $imageAnnotator = new ImageAnnotatorClient();

        # annotate the image
        $response = $imageAnnotator->objectLocalization(file_get_contents($path));
        $objects = $response->getLocalizedObjectAnnotations();

        $objectArr = [];
        foreach($objects as $object){
            $objectArr[] = ["name" => $object->getName(), "score" => $object->getScore()];
        }
        return $objectArr;
    }
}