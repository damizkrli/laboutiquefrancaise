<?php

namespace App\Classe;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class Cart
{

    private RequestStack $stack;
    private EntityManagerInterface $entityManager;

    public function __construct(
        RequestStack $stack,
        EntityManagerInterface $entityManager
    ) {
        $this->stack = $stack;
        $this->entityManager = $entityManager;
    }

    /**
     * Permet de sécuriser l'action de l'ajout au panier.
     * dans l'URL : add/cart/456415 qui n'existe pas, alors Doctrine supprime l'article avec l'id 456415
     * @return array
     */
    public function getFull()
    {
        $cartComplete = [];

        if ($this->get()) {
            foreach ($this->get() as $id => $quantity) {
                $product_object = $this->entityManager->getRepository(Product::class)->findOneById($id);

                if (!($product_object)) {
                    $this->delete($id);
                    continue;
                }

                $cartComplete[] = [
                        'product' => $product_object,
                        'quantity' => $quantity
                    ];

            }
        }

        return $cartComplete;
    }

    /**
     * Permet d'augmenter le nombre d'article dans le panier
     * @param $id
     * @return void
     */
    public function add($id)
    {
        $session = $this->stack->getSession();
        $cart = $session->get('cart', []);

        if (!empty($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        $session->set('cart' , $cart);
    }

    public function get()
    {
        return $this->stack->getSession()->get('cart');
    }

    public function remove(){

        return $this->stack->getSession()->remove('cart');
    }

    /**
     *
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        $cart = $this->stack->getSession()->get('cart', []);

        unset($cart[$id]);

        return $this->stack->getSession()->set('cart', $cart);
    }

    /**
     * Permet de diminuer le nombre d'article dans le panier
     * @param $id
     * @return mixed
     */
    public function decrease($id)
    {
        $cart = $this->stack->getSession()->get('cart', []);

        if ($cart[$id] > 1) {
            $cart[$id]--;
        } else {
            unset($cart[$id]);
        }

        return $this->stack->getSession()->set('cart', $cart);
    }
}