<?php

use Phalcon\Mvc\Controller;

use GuzzleHttp\Client;

class IndexController extends Controller
{
    public function indexAction()
    {
    }
    public function addProductAction()
    {
        if ($this->request->getPost()) {
            $keyForFieldName = $this->request->getPost('field_name');
            $valueForFieldValue = $this->request->getPost('field_value');
            $variationSize = $this->request->getPost('size');
            $variationColor = $this->request->getPost('color');
            $variationPrice = $this->request->getPost('variation_price');
            $additional_fields = array_combine($keyForFieldName, $valueForFieldValue);
            $variations = array(
                "size" => $variationSize,
                "color" => $variationColor,
                "price" => $variationPrice
            );

            $postdata = $this->request->getPost();
            if (!(empty($postdata['product_name']) || empty($postdata['tags']) || empty($postdata['price']) || empty($postdata['stock']))) {

                $success = $document = array(
                    "product_name" => $postdata['product_name'],
                    "product_category" => $postdata['tags'],
                    "price" => $postdata['price'],
                    "stock" => $postdata['stock'],
                    "additional_fields" => $additional_fields,
                    "variations" => $variations
                );
                $this->mongo->insertOne($document);
            }

            if ($success) {
                $this->session->msg = "<p class='alert-success'>Product added successfully!!</p>";
                $this->response->redirect('/index');
            } else {
                $this->session->msg = "<p class='alert-danger'>*All fields are required!!</p>";
                $this->response->redirect('/index');
            }


            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        }
    }
    public function productsListAction()
    {
        $resultt = $this->mongo->find();
        $this->view->allResult = $resultt;
        if ($this->request->getPost('search')) {
            $searchByProductName = $this->request->getPost('searchByProductName');
            $result = array();
            foreach ($resultt as $k => $v) {

                if (strtolower($v->product_name) == strtolower($searchByProductName)) {
                    array_push($result, $v);
                } else {
                    $this->view->error = "<h3 class='alert alert-danger'>No Product Found !!</h3>";
                }
            }
            $this->view->result = $result;
        }
        if ($this->request->getPost('delete')) {
            $id = $this->request->getPost('id');
            $data = $this->mongo->deleteOne([
                "_id" => new MongoDB\BSON\ObjectID($id)
            ]);

            $this->response->redirect('/index/productsList');
        }
        if ($this->request->getPost('edit')) {
            $id = $this->request->getPost('id');
            $data = $this->mongo->findOne([
                "_id" => new MongoDB\BSON\ObjectID($id)
            ]);
            $postdata = $this->request->getPost();
            // if (!(empty($postdata['product_name']) || empty($postdata['tags']) || empty($postdata['price']) || empty($postdata['stock']))) {
                $this->mongo->updateOne(["_id" => new MongoDB\BSON\ObjectID($id)], ['$set' => $postdata]);
                // $this->session->saved = '<span class="text-success">Saved</span>';
                $this->response->redirect('/index/productsList');
            // }
        }
    }
}
