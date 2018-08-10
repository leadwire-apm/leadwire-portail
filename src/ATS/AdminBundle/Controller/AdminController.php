<?php declare(strict_types=1);

namespace ATS\AdminBundle\Controller;

use ATS\AdminBundle\Form\DocumentType;
use ATS\AdminBundle\Manager\AdminManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\DocumentManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{
    /**
     * @Route("/", methods="GET")
     */
    public function indexAction(Request $request, ManagerRegistry $managerRegistry)
    {
        $documentManager = $managerRegistry->getManager();

        $data = $this->getMetaData($documentManager);

        return $this->render(
            'AdminBundle:Default:dashboard.html.twig',
            [
                'data' => $data,
                'bundled' => $this->getBundledDocuments($data),
                'admin_routes_prefix' => $this->container->getParameter('admin_routes_prefix'),
            ]
        );
    }

    public function listEntitiesAction(Request $request, ManagerRegistry $managerRegistry, $pageNumber = 1)
    {
        $documentManager = $managerRegistry->getManager();
        $origin = $request->get('_route');
        $entityClass = $this->resolveEntityClass($origin, $documentManager, 'list');
        $manager = new AdminManager(
            $managerRegistry,
            $entityClass
        );

        $data = $manager->paginate([], $pageNumber);
        $totalCount = $manager->count();

        return $this->render(
            'AdminBundle:Default:list.html.twig',
            [
                'data' => $data,
                'bundled' => $this->getBundledDocuments($this->getMetaData($documentManager)),
                'admin_routes_prefix' => $this->container->getParameter('admin_routes_prefix'),
                'createURL' => str_replace('list', 'new', $origin),
                'editURL' => str_replace('list', 'edit', $origin),
                'deleteURL' => str_replace('list', 'delete', $origin),
                'class' => $manager->getDocumentClass(),
                'total_count' => $totalCount,
                'items_per_page' => 20,
                'total_page_count' => ceil($totalCount / 20),
                'previous' => $pageNumber - 1,
                'next' => $pageNumber + 1,
            ]
        );
    }

    public function createEntityAction(Request $request, ManagerRegistry $managerRegistry, $id = null)
    {
        $documentManager = $managerRegistry->getManager();
        $origin = $request->get('_route');
        $entityClass = $this->resolveEntityClass($origin, $documentManager, "new");
        $manager = new AdminManager(
            $managerRegistry,
            $entityClass
        );

        $entity = new $entityClass();
        $form = $this->createForm(
            DocumentType::class,
            $entity,
            array(
                'className' => $manager->getDocumentClass(),
                'adminGuesser' => $this->container->get('admin.type_guesser'),
                'action' => $this->generateUrl($origin, ['id' => $entity->getId()]),
            )
        );

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->getClickedButton() && 'cancel' === $form->getClickedButton()->getName()) {
                return $this->redirect($this->generateUrl(preg_replace('/_(edit|new)_/', '_list_', $origin)));
            }

            if ($form->isSubmitted() && $form->isValid()) {
                $manager->update($entity);

                $this->addFlash(
                    'success',
                    sprintf(
                        '%s document with ID %s successfully updated',
                        get_class($entity),
                        $id
                    )
                );

                return $this->handleRedirect($origin, $form, $id);
            }
        }

        return $this->render(
            'AdminBundle:Default:edit.html.twig',
            [
                'form' => $form->createView(),
                'bundled' => $this->getBundledDocuments($this->getMetaData($documentManager)),
                'admin_routes_prefix' => $this->container->getParameter('admin_routes_prefix'),
            ]
        );
    }

    public function editEntityAction(Request $request, ManagerRegistry $managerRegistry, $id)
    {
        $documentManager = $managerRegistry->getManager();
        $origin = $request->get('_route');
        $entityClass = $this->resolveEntityClass($origin, $documentManager, "edit");
        $manager = new AdminManager(
            $managerRegistry,
            $entityClass
        );
        $entity = $manager->getOneBy(['id' => $id]);

        $form = $this->createForm(
            DocumentType::class,
            $entity,
            array(
                'className' => $manager->getDocumentClass(),
                'adminGuesser' => $this->container->get('admin.type_guesser'),
                'action' => $this->generateUrl($origin, ['id' => $entity->getId()]),
            )
        );

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->getClickedButton() && 'cancel' === $form->getClickedButton()->getName()) {
                return $this->redirect($this->generateUrl(preg_replace('/_(edit|new)_/', '_list_', $origin)));
            }

            if ($form->isSubmitted() && $form->isValid()) {
                $manager->update($entity);

                $this->addFlash(
                    'success',
                    sprintf(
                        '%s document with ID %s successfully updated',
                        get_class($entity),
                        $id
                    )
                );

                return $this->handleRedirect($origin, $form, $id);
            }
        }

        return $this->render(
            'AdminBundle:Default:edit.html.twig',
            [
                'form' => $form->createView(),
                'bundled' => $this->getBundledDocuments($this->getMetaData($documentManager)),
                'admin_routes_prefix' => $this->container->getParameter('admin_routes_prefix'),
            ]
        );
    }

    public function deleteEntityAction(Request $request, ManagerRegistry $managerRegistry, $id)
    {
        $documentManager = $managerRegistry->getManager();
        $origin = $request->get('_route');
        $entityClass = $this->resolveEntityClass($origin, $documentManager, "delete");
        $manager = new AdminManager(
            $managerRegistry,
            $entityClass
        );

        $entity = $manager->getOneBy(['id' => $id]);

        if ($entity) {
            $manager->delete($entity);
        }

        return $this->redirect($this->generateUrl(preg_replace('/_delete_/', '_list_', $origin)));
    }

    private function resolveEntityClass($origin, DocumentManager $documentManager, $type = "list")
    {
        $metas = $this->getMetaData($documentManager);
        $prefix = $this->container->getParameter('admin_routes_prefix') . "_" . $type . "_";
        $entityIdentifier = str_replace('_', '\\', str_replace($prefix, '', $origin));
        $entityClassName = array_filter($metas, function ($element) use ($entityIdentifier) {
            return strtolower($element) == $entityIdentifier;
        });

        $entityClassName = reset($entityClassName);

        return $entityClassName;
    }

    private function getMetaData(DocumentManager $documentManager)
    {
        $metas = $documentManager
            ->getMetadataFactory()
            ->getAllMetadata();

        $data = array_map(function ($meta) {
            if (!$meta->isMappedSuperclass && !$meta->isEmbeddedDocument) {
                return $meta->getName();
            }

            return null;
        }, $metas);

        return array_filter($data, function ($element) {
            return $element != null;
        });
    }

    private function handleRedirect($scope, $form, $id)
    {
        if ($form->getClickedButton() && 'saveAndBackToList' === $form->getClickedButton()->getName()) {
            return $this->redirect($this->generateUrl(preg_replace('/_(edit|new)_/', '_list_', $scope)));
        }

        return $this->redirect($this->generateUrl($scope, ['id' => $id]));
    }

    private function getBundledDocuments($metadata)
    {
        $bundled = [];

        foreach ($metadata as $meta) {
            $bundleName = preg_replace('/\\\\Document\\\\.*/', '', $meta);
            $displayBundleName = explode('\\', $bundleName);
            $displayBundleName = end($displayBundleName);
            $documentName = preg_replace('/.*Document\\\\/', '', $meta);
            $bundled[$displayBundleName][] = [$bundleName, $documentName];
        }

        return $bundled;
    }
}
