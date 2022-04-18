<?php

use Phalcon\Mvc\Controller;

class OrderController extends Controller
{
    public function indexAction()
    {
        $products = $this->mongo->products->find();
        $this->view->products = $products;
        $postdata = $this->request->getPost();
        if ($this->request->getPost('add_order')) {
            if ($postdata['product'] || $postdata['customer_name'] || $postdata['quantity']) {
                $postdata = array(
                    "product_id" => $postdata['product'],
                    "customer_name" => $postdata['customer_name'],
                    "quantity" => $postdata['quantity'],
                    "status" => "paid",
                    "order_date" => date('d/m/Y')

                );
                $this->mongo->orders->insertOne($postdata);
                $this->view->msg = "<p class='text-success'>*Order Added Successfully!!</p>";
            } else {
                $this->view->msg = "<p class='text-danger'>*Please fill all fields!!</p>";
            }
        }
    }
    public function listAction()
    {
        $orders = $this->mongo->orders->find();
        if ($this->request->getPost('filter_by_status')) {
            $filter_by_status = $this->request->getPost('filter_by_status');
            $orders = array('status' => $filter_by_status);
            $orders = $this->mongo->orders->find($orders);
            $this->view->orders = $orders;
        }
        // else {
        //     $this->view->orders = $orders;
        // }

        if ($this->request->getPost('filter_by_date')) {
            $filter_by_date = $this->request->getPost('filter_by_date');
            if ($filter_by_date == 'today') {
                $orders = array('order_date' => date('d/m/Y'));
                $orders = $this->mongo->orders->find($orders);
                $this->view->orders = $orders;
            }
            if ($filter_by_date == 'this_week') {
                $start_date = date("d/m/Y", strtotime("-1 week"));
                $end_date = date("d/m/Y");
                $orders = array('order_date' => ['$gte' => $start_date, '$lte' => $end_date]);
                $orders = $this->mongo->orders->find($orders);
                $this->view->orders = $orders;
            }
            if ($filter_by_date == 'this_month') {
                $start_date = date("d/m/Y", strtotime("first day of this month"));
                $end_date = date("d/m/Y");
                // die($end_date);
                $orders = array('order_date' => ['$gte' => $start_date, '$lte' => $end_date]);
                $orders = $this->mongo->orders->find($orders);
                $this->view->orders = $orders;
            }
        }



        if ($this->request->getPost('status')) {

            $id = $this->request->getPost('id');
            $status = $this->request->getPost('status');
            $postdata = array(
                "status" => $status

            );
            $this->mongo->orders->updateOne(["_id" => new MongoDB\BSON\ObjectID($id)], ['$set' => $postdata]);
            $this->response->redirect('/order/list');
        } else {
            $this->view->orders = $orders;
        }
    }
}
