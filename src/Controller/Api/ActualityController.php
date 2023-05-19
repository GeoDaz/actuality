<?php

namespace App\Controller\Api;

use App\Entity\Actuality;
use App\Normalizer\EntityNormalizer;
use App\Repository\ActualityRepository;
use App\Service\DescriptionParser;
use App\Service\ErrorManager;
use App\Service\ImageArticleManager;
use App\Service\GenRequestManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ActualityController extends AbstractController
{
	const ACTUALITY_IMAGE_SIZE        = 300;
	const ACTUALITY_TEASER_IMAGE_SIZE = 250;

	/** @var ActualityRepository */
	private $repo;

	public function __construct(ActualityRepository $repo)
	{
		$this->repo = $repo;
	}

	/**
	 * @Route("/actualities", name="actualities", methods={"GET"})
	 */
	public function getActualities(Request $request)
	{
		if (!empty($request->get('maxLength'))) {
			$actualities = $this->repo->findWithMax($request->get('maxLength'));

		} else {
			$criteria = null;
			if (!empty($request->get('tags'))) {
				$criteria = explode(',', $request->get('tags'));
			}
			$actualities = $this->repo->findWithQuery($criteria);
		}

		return $this->json(
			['actualities' => $actualities],
			Response::HTTP_OK,
			[],
			['groups' => 'read:list']
		);
	}

	/**
	 * @Route("/actualities/{id}", name="actuality_by_id", methods={"GET"})
	 */
	public function getActualityById($id)
	{
		$actuality = $this->repo->findOne($id);

		if (empty($actuality)) {
			return new JsonResponse(
				['message' => "Mauvais identifiant"],
				Response::HTTP_NOT_FOUND
			);
		}

		return $this->json(
			['actuality' => $actuality],
			Response::HTTP_OK,
			[],
			['groups' => ['read:article', 'read:name']]
		);
	}

	/**
	 * @Route("/actualities/{id}/images", name="actuality_images", methods={"POST"})
	 */
	public function setActualityImages($id, Request $request, ImageArticleManager $imageArticleManager)
	{
		$actuality = $this->repo->findOne($id);

		if (empty($actuality)) {
			return new JsonResponse(
				['message' => "Mauvais identifiant"],
				Response::HTTP_NOT_FOUND
			);
		}

		if (!count($request->files)) {
			return $this->json(
				['message' => 'Aucune pièce fournie.'],
				Response::HTTP_BAD_REQUEST
			);
		}

		$imageArticleManager->setImagesToEntity($actuality, $request->files, 'actualities');

		$this->getDoctrine()->getManager()->flush();

		return $this->json(
			['actuality' => $actuality],
			Response::HTTP_OK,
			[],
			['groups' => 'read:article']
		);
	}

	/**
	 * @Route("/actualities", name="insert_actuality", methods={"POST"})
	 */
	public function insertActuality(
		Request $request,
		SerializerInterface $serializer,
		ValidatorInterface $validator,
		ErrorManager $errorManager
	) {
		$json = $request->getContent();
		try {
			/** @var Actuality $actuality */
			$actuality = $serializer->deserialize($json, Actuality::class, 'json');
		} catch (NotEncodableValueException $e) {
			// return $this->json(
			// 	['message' => $e->getMessage()],
			// 	Response::HTTP_BAD_REQUEST
			// );
		}

		$errors = $validator->validate($actuality);
		if (count($errors) > 0) {
			return $this->json(
				['errors' => $errorManager->parseErrors($errors)],
				Response::HTTP_BAD_REQUEST
			);
		}

		$this->repo->insert($actuality);

		return $this->json(
			['message' => 'Actualité enregistrée', 'actuality' => $actuality],
			Response::HTTP_OK,
			[],
			['groups' => 'read:article']
		);
	}

	/**
	 * @Route("/actualities/{id}", name="update_actuality", methods={"PUT"})
	 */
	public function updateActuality(
		$id,
		Request $request,
		ImageArticleManager $imageArticleManager,
		SerializerInterface $serializer,
		ValidatorInterface $validator,
		ErrorManager $errorManager
	) {
		$actuality = $this->repo->findOne($id);
		if (empty($actuality)) {
			return new JsonResponse(
				['message' => "Mauvais identifiant"],
				Response::HTTP_NOT_FOUND
			);
		}

		$originalImages      = $actuality->getImages();

		$json = $request->getContent();
		try {
			/** @var Actuality $actuality */
			$actuality = $serializer->deserialize(
				$json,
				Actuality::class,
				'json',
				[
					AbstractNormalizer::OBJECT_TO_POPULATE => $actuality,
					EntityNormalizer::UPDATE_ENTITIES => [Actuality::class]
				]
			);
		} catch (NotEncodableValueException $e) {
			// return $this->json(
			// 	['message' => $e->getMessage()],
			// 	Response::HTTP_BAD_REQUEST
			// );
		}

		$errors = $validator->validate($actuality);
		if (count($errors) > 0) {
			return $this->json(
				['errors' => $errorManager->parseErrors($errors)],
				Response::HTTP_BAD_REQUEST
			);
		}


		$imageArticleManager->removeImagesFromEntity($actuality, 'actualities', $originalImages);
		
		$actuality->setUpdateDate(new \DateTime());
		$this->getDoctrine()->getManager()->flush();

		return $this->json(
			['message' => 'Actualité mise à jour', 'actuality' => $actuality],
			Response::HTTP_OK,
			[],
			['groups' => 'read:article']
		);
	}

	/**
	 * @Route("/actualities/{id}", name="delete_actuality", methods={"DELETE"})
	 */
	public function deleteActuality($id, ImageArticleManager $imageArticleManager)
	{
		$actuality = $this->repo->find($id);
		if (empty($actuality)) {
			return $this->json(
				['message' => "Mauvais identifiant"],
				Response::HTTP_NOT_FOUND
			);
		}

		$imageArticleManager->removeImagesFromEntity($actuality, 'actualities');

		$this->repo->delete($actuality);

		return new JsonResponse(
			['message' => "Actualité $id supprimée", 'id' => $id],
			Response::HTTP_OK
		);
	}
}
