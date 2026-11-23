<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryDeleteType;
use App\Form\CategoryFilterType;
use App\Form\CategoryType;
use App\Form\Model\CategoryData;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/categories', name: 'app_category_')]
final class CategoryController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request, CategoryRepository $categories): Response
    {
        $filterForm = $this->createForm(CategoryFilterType::class, null, [
            'action' => $this->generateUrl('app_category_index'),
        ]);
        $filterForm->handleRequest($request);
        $name = $filterForm->get('name')->getData();

        return $this->render('category/index.html.twig', [
            'categories' => $categories->searchByName($name),
            'filterForm' => $filterForm,
            'deleteForm' => $this->createForm(CategoryDeleteType::class),
            'searchName' => trim((string) $name),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_AGENT', message: 'Necesitas permisos de agente para crear categorías.')]
    public function new(Request $request, CategoryRepository $categories, EntityManagerInterface $entityManager): Response
    {
        $data = new CategoryData();
        $form = $this->createForm(CategoryType::class, $data, ['submit_label' => 'Crear categoría']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $name = trim($data->name);
            if ($categories->findOneBy(['name' => $name]) !== null) {
                $form->get('name')->addError(new FormError('Ya existe una categoría con ese nombre.'));
            } else {
                $category = new Category($name);
                $entityManager->persist($category);
                $entityManager->flush();
                $this->addFlash('success', 'Categoría creada correctamente.');

                return $this->redirectToRoute('app_category_index');
            }
        }

        return $this->render('category/new.html.twig', ['form' => $form]);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_AGENT', message: 'Necesitas permisos de agente para modificar categorías.')]
    public function edit(int $id, Request $request, CategoryRepository $categories, EntityManagerInterface $entityManager): Response
    {
        $category = $categories->find($id);
        if (!$category instanceof Category) {
            throw $this->createNotFoundException('No existe la categoría.');
        }

        $data = CategoryData::fromCategory($category);
        $form = $this->createForm(CategoryType::class, $data, ['submit_label' => 'Guardar cambios']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $name = trim($data->name);
            $existing = $categories->findOneBy(['name' => $name]);
            if ($existing !== null && $existing->getId() !== $category->getId()) {
                $form->get('name')->addError(new FormError('Ya existe una categoría con ese nombre.'));
            } else {
                $category->changeName($name);
                $entityManager->flush();
                $this->addFlash('success', 'Categoría actualizada correctamente.');

                return $this->redirectToRoute('app_category_index');
            }
        }

        return $this->render('category/edit.html.twig', ['category' => $category, 'form' => $form]);
    }

    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_AGENT', message: 'Necesitas permisos de agente para eliminar categorías.')]
    public function delete(int $id, Request $request, CategoryRepository $categories, EntityManagerInterface $entityManager): Response
    {
        $category = $categories->find($id);
        if (!$category instanceof Category) {
            throw $this->createNotFoundException('No existe la categoría.');
        }

        $form = $this->createForm(CategoryDeleteType::class);
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            throw $this->createNotFoundException('El formulario de eliminación no es válido.');
        }

        $entityManager->remove($category);
        $entityManager->flush();
        $this->addFlash('success', 'Categoría eliminada correctamente. Los tickets asociados se han conservado sin categoría.');

        return $this->redirectToRoute('app_category_index');
    }
}
