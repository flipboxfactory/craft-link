<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://raw.githubusercontent.com/flipboxfactory/craft-link/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-link
 */

namespace flipbox\craft\link\controllers;

use Craft;
use craft\web\Controller;
use flipbox\craft\link\Link;
use yii\web\HttpException;
use yii\web\Response;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class TypeController extends Controller
{
    /**
     * @return Response
     * @throws HttpException
     * @throws \Twig\Error\LoaderError
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSettings(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $view = $this->getView();

        $type = Link::getInstance()->findType(
            Craft::$app->getRequest()->getRequiredBodyParam('type')
        );

        if (!$type) {
            throw new HttpException("Type not found");
        }

        // Allow explicit setting of the identifier
        if ($identifier = Craft::$app->getRequest()->getBodyParam('identifier')) {
            $type->setIdentifier($identifier);
        }

        $html = $view->renderTemplate(
            'link/_components/fieldtypes/Link/type',
            [
                'type' => $type,
                'namespace' => Craft::$app->getRequest()->getRequiredBodyParam('namespace')
            ]
        );

        return $this->asJson(
            [
            'label' => $type::displayName(),
            'paneHtml' => $html,
            'headHtml' => $view->getHeadHtml(),
            'footHtml' => $view->getBodyHtml(),
            ]
        );
    }
}
