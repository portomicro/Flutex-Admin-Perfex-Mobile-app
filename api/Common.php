<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require __DIR__.'/REST_Controller.php';

class Common extends REST_Controller {
    public function __construct()
    {
        parent::__construct();
    }

    public function data_get($type = "")
    {
    	$allowed_type = ["expense_category", "payment_mode", "tax_data", "leads_sources", "leads_statuses", "proposal_statuses", "ticket_departments", "ticket_priorities", "ticket_services", "currencies", "countries", "client_groups"];
        if (empty($type) || !in_array($type, $allowed_type)) {
            // Set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'Not valid data'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
        $data = $this->{$type}();
        if (empty($data)) {
            // Set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'No data were found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
        
        $this->response($data, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code  

    }

    /**
     * @api {get} api/common/expense_category Request Expense category
     * @apiVersion 0.3.0
     * @apiName GetExpense category
     * @apiGroup Expense Categories
     *
     * @apiHeader {String} Authorization Basic Access Authentication token.
     *
     * @apiSuccess {Array} Expense category information.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *
     *        [
     *            {
     *                "id": "1",
     *                "name": "cloud server",
     *                "description": "AWS server"
     *            },
     *            {
     *                "id": "2",
     *                "name": "website domain",
     *                "description": "domain Managment and configurations"
     *            }
     *        ]
     *
     *     }
     *
     * @apiError {Boolean} status Request status.
     * @apiError {String} message No data were found.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "No data were found"
     *     }
     */

    public function expense_category()
    {
    	$this->load->model('expenses_model');
		return $this->expenses_model->get_category();
    }

    /**
     * @api {get} api/common/payment_mode Request Payment Modes
     * @apiVersion 0.3.0
     * @apiName GetPayment Mode
     * @apiGroup Payment Modes
     *
     * @apiHeader {String} Authorization Basic Access Authentication token.
     *
     * @apiSuccess {Array} Payment Modes.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *    [
     *        {
     *            "id": "1",
     *            "name": "Bank",
     *            "description": null,
     *            "show_on_pdf": "0",
     *            "invoices_only": "0",
     *            "expenses_only": "0",
     *            "selected_by_default": "1",
     *            "active": "1"
     *        }
     *    ]
     *
     * @apiError {Boolean} status Request status.
     * @apiError {String} message No data were found.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "No data were found"
     *     }
     */
    public function payment_mode()
    {
        $this->load->model('payment_modes_model');
		return $this->payment_modes_model->get('', [
            'invoices_only !=' => 1,
        ]);
    }

    /**
     * @api {get} api/common/tax_data Request Taxes
     * @apiVersion 0.3.0
     * @apiName GetTaxes
     * @apiGroup Taxes
     *
     * @apiHeader {String} Authorization Basic Access Authentication token.
     *
     * @apiSuccess {Array} Tax information.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
    *    [
    *        {
    *            "id": "4",
    *            "name": "PAYPAL",
    *            "taxrate": "5.00"
    *        },
    *        {
    *            "id": "1",
    *            "name": "CGST",
    *            "taxrate": "9.00"
    *        },
    *        {
    *            "id": "2",
    *            "name": "SGST",
    *            "taxrate": "9.00"
    *        },
    *        {
    *            "id": "3",
    *            "name": "GST",
    *            "taxrate": "18.00"
    *        }
    *    ]
     *
     * @apiError {Boolean} status Request status.
     * @apiError {String} message No data were found.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "No data were found"
     *     }
     */

    public function tax_data()
    {
    	$this->load->model('taxes_model');
		return $this->taxes_model->get();
    }

    public function leads_sources()
    {
    	$this->load->model('leads_model');
		return $this->leads_model->get_source();
    }

    public function leads_statuses()
    {
    	$this->load->model('leads_model');
		return $this->leads_model->get_status();
    }

    public function proposal_statuses()
    {
    	$this->load->model('proposals_model');
		return $this->proposals_model->get_statuses();
    }

    public function ticket_departments()
    {
    	$this->load->model('departments_model');
		return $this->departments_model->get();
    }

    public function ticket_priorities()
    {
    	$this->load->model('tickets_model');
		return $this->tickets_model->get_priority();
    }

    public function ticket_services()
    {
        $this->load->model('tickets_model');
		return $this->tickets_model->get_service();
    }

    public function currencies()
    {
        $this->load->model('currencies_model');
		return $this->currencies_model->get();
    }

    public function countries()
    {
		return get_all_countries();
    }

    public function client_groups()
    {
		$this->load->model('client_groups_model');
		return $this->client_groups_model->get_groups();
    }
}

/* End of file Common.php */
/* Location: ./application/controllers/Common.php */