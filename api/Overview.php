<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions

/** @noinspection PhpIncludeInspection */
require __DIR__ . '/REST_Controller.php';

class Overview extends REST_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('invoices_model');
        $this->load->model('Staff_model');
    }
    
    public function data_get()
    {
        
        // Perfex CRM Logo
        $perfex_logo = get_option('company_logo');
        $perfex_logo_dark = get_option('company_logo_dark');
        
        // Dashboard Overview Data << START >>
        $total_invoices = total_rows(db_prefix() . 'invoices', 'status NOT IN (5,6)' . (!has_permission('invoices', '', 'view') ? ' AND ' . get_invoices_where_sql_for_staff(get_staff_user_id()) : ''));
        $total_invoices_awaiting_payment = total_rows(db_prefix() . 'invoices', 'status NOT IN (2,5,6)' . (!has_permission('invoices', '', 'view') ? ' AND ' . get_invoices_where_sql_for_staff(get_staff_user_id()) : ''));
        $percent_total_invoices_awaiting_payment = ($total_invoices > 0 ? number_format(($total_invoices_awaiting_payment * 100) / $total_invoices, 2) : 0);

        $where = '';
        if (!is_admin()) {
            $where .= '(addedfrom = ' . get_staff_user_id() . ' OR assigned = ' . get_staff_user_id() . ')';
        }
        
        $total_leads = total_rows(db_prefix() . 'leads', ($where == '' ? 'junk=0' : $where .= ' AND junk =0'));
        if ($where == '') {
            $where .= 'status=1';
        } else {
            $where .= ' AND status =1';
        }
        $total_leads_converted = total_rows(db_prefix() . 'leads', $where);
        $percent_total_leads_converted = ($total_leads > 0 ? number_format(($total_leads_converted * 100) / $total_leads, 2) : 0);

        $_where = '';
        $project_status = get_project_status_by_id(2);
        if (!has_permission('projects', '', 'view')) {
            $_where = 'id IN (SELECT project_id FROM ' . db_prefix() . 'project_members WHERE staff_id=' . get_staff_user_id() . ')';
        }
        $total_projects = total_rows(db_prefix() . 'projects', $_where);
        $where = ($_where == '' ? '' : $_where . ' AND ') . 'status = 2';
        $total_projects_in_progress = total_rows(db_prefix() . 'projects', $where);
        $percent_in_progress_projects = ($total_projects > 0 ? number_format(($total_projects_in_progress * 100) / $total_projects, 2) : 0);

        $_where = '';
        if (!has_permission('tasks', '', 'view')) {
            $_where = db_prefix() . 'tasks.id IN (SELECT taskid FROM ' . db_prefix() . 'task_assigned WHERE staffid = ' . get_staff_user_id() . ')';
        }
        $total_tasks = total_rows(db_prefix() . 'tasks', $_where);
        $where = ($_where == '' ? '' : $_where . ' AND ') . 'status != ' . Tasks_model::STATUS_COMPLETE;
        $total_not_finished_tasks = total_rows(db_prefix() . 'tasks', $where);
        $percent_not_finished_tasks = ($total_tasks > 0 ? number_format(($total_not_finished_tasks * 100) / $total_tasks, 2) : 0);
        // Dashboard Overview Data << END >>
        
        // Staff Information
        $staff = $this->db->where('staffid', get_staff_user_id())->get(db_prefix() . 'staff')->row();
        $staff->profile_image = staff_profile_image_url($staff->staffid);
        
        // Menu Items
        $menu_items = [
            'customers' => has_permission('customers', '', 'view') || (have_assigned_customers() || (!have_assigned_customers() && has_permission('customers', '', 'create'))),
            'proposals' => (has_permission('proposals', '', 'view') || has_permission('proposals', '', 'view_own')) || (staff_has_assigned_proposals() && get_option('allow_staff_view_proposals_assigned') == 1),
            'estimates' => (has_permission('estimates', '', 'view') || has_permission('estimates', '', 'view_own')) || (staff_has_assigned_estimates() && get_option('allow_staff_view_estimates_assigned') == 1),
            'invoices' => (has_permission('invoices', '', 'view') || has_permission('invoices', '', 'view_own')) || (staff_has_assigned_invoices() && get_option('allow_staff_view_invoices_assigned') == 1),
            'payments' => has_permission('payments', '', 'view') || has_permission('invoices', '', 'view_own') || (get_option('allow_staff_view_invoices_assigned') == 1 && staff_has_assigned_invoices()),
            'credit_notes' => has_permission('credit_notes', '', 'view') || has_permission('credit_notes', '', 'view_own'),
            'items' => has_permission('items', '', 'view'),
            'subscriptions' => has_permission('subscriptions', '', 'view') || has_permission('subscriptions', '', 'view_own'),
            'expenses' => has_permission('expenses', '', 'view') || has_permission('expenses', '', 'view_own'),
            'contracts' => has_permission('contracts', '', 'view') || has_permission('contracts', '', 'view_own'),
            'projects' => true,
            'tasks' => true,
            'tickets' => (!is_staff_member() && get_option('access_tickets_to_none_staff_members') == 1) || is_staff_member(),
            'leads' => is_staff_member(),
            'staff' => has_permission('staff', '', 'view')
        ];

        $this->response([
            'status' => true,
            'message' => 'Data retrieved successfully',
            'overview' => [
                'perfex_logo' => ($perfex_logo != '' ? base_url('uploads/company/' . $perfex_logo) : ''),
                'perfex_logo_dark' => ($perfex_logo_dark != '' ? base_url('uploads/company/' . $perfex_logo_dark) : ''),
                'total_invoices' => $total_invoices,
                'invoices_awaiting_payment_total' => $total_invoices_awaiting_payment,
                'invoices_awaiting_payment_percent' => $percent_total_invoices_awaiting_payment,
                'total_leads' => $total_leads,
                'leads_converted_total' => $total_leads_converted,
                'leads_converted_percent' => $percent_total_leads_converted,
                'total_projects' => $total_projects,
                'projects_in_progress_total' => $total_projects_in_progress,
                'projects_in_progress_percent' => $percent_in_progress_projects,
                'total_tasks' => $total_tasks,
                'tasks_not_finished_total' => $total_not_finished_tasks,
                'tasks_not_finished_percent' => $percent_not_finished_tasks
            ],
            'data' => [
                'invoices' => $this->invoices_summary(),
                'estimates' => $this->estimates_summary(),
                'proposals' => $this->proposals_summary(),
                'projects' => $this->projects_summary(),
                'customers' => $this->customers_summary(),
                'leads' => $this->leads_summary(),
                'tickets' => $this->tickets_summary(),
            ],
            'staff' => $staff,
            'menu_items' => $menu_items
        ], REST_Controller::HTTP_OK);
    }

    public function invoices_summary()
    {
        if (has_permission('invoices', '', 'view')) {
            // Invoices Overview
            $invoices = [];
            $invoice_statuses = $this->invoices_model->get_statuses();
            foreach ($invoice_statuses as $status) {
                $percent_data = get_invoices_percent_by_status($status);
                array_push($invoices, [
                    'status' => format_invoice_status($status, '', false),
                    'total' => $percent_data['total_by_status'],
                    'percent' => $percent_data['percent']
                ]);
            }
            return $invoices;
        }

        return [];
    }

    public function estimates_summary()
    {
        if (has_permission('estimates', '', 'view')) {
            // Estimates Overview
            $estimates = [];
            $this->load->model('estimates_model');
            $estimate_statuses = $this->estimates_model->get_statuses();
    
            array_splice($estimate_statuses, 1, 0, 'not_sent');
            foreach ($estimate_statuses as $status) {
                $percent_data = get_estimates_percent_by_status($status);
                array_push($estimates, [
                    'status' => format_estimate_status($status, '', false),
                    'total' => $percent_data['total_by_status'],
                    'percent' => $percent_data['percent']
                ]);
            }
            return $estimates;
        }

        return [];
    }

    public function proposals_summary()
    {
        if (has_permission('proposals', '', 'view')) {
            // Proposals Overview
            $proposals = [];
            $this->load->model('proposals_model');
            $proposal_statuses = $this->proposals_model->get_statuses();
            
            foreach ($proposal_statuses as $status) {
                $percent_data = get_proposals_percent_by_status($status);
                array_push($proposals, [
                    'status' => format_proposal_status($status, '', false),
                    'total' => $percent_data['total_by_status'],
                    'percent' => $percent_data['percent']
                ]);
            }
            return $proposals;
        }

        return [];
    }

    public function projects_summary()
    {
        if (has_permission('projects', '', 'view')) {
            // Projects Overview
            $projects = [];
            $this->load->model('projects_model');
            $project_statuses = $this->projects_model->get_project_statuses();
    
            $_where = '';
            if (!has_permission('projects', '', 'view')) {
                $_where = 'id IN (SELECT project_id FROM ' . db_prefix() . 'project_members WHERE staff_id=' . get_staff_user_id() . ')';
            }
    
            foreach ($project_statuses as $key => $status) {
                $where = ($_where == '' ? '' : $_where . ' AND ') . 'status = ' . $status['id'];
                array_push($projects, [
                    'status' => $status['name'],
                    'total' => total_rows(db_prefix() . 'projects', $where),
                    'percent' => strval(total_rows(db_prefix() . 'projects', $where) / total_rows(db_prefix() . 'projects') * 100)
                ]);
            }
            return $projects;
        }

        return [];
    }

    public function customers_summary()
    {
        if (has_permission('customers', '', 'view') || have_assigned_customers()) {
            $where_summary = '';
            if (!has_permission('customers', '', 'view')) {
                $where_summary = ' AND userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_admins WHERE staff_id=' . get_staff_user_id() . ')';
            }
            return [
                'customers_total' => total_rows(db_prefix() . 'clients', ($where_summary != '' ? substr($where_summary, 5) : '')),
                'customers_active' => total_rows(db_prefix() . 'clients', 'active=1' . $where_summary),
                'customers_inactive' => total_rows(db_prefix() . 'clients', 'active=0' . $where_summary),
                'contacts_active' => total_rows(db_prefix() . 'contacts', 'active=1' . $where_summary),
                'contacts_inactive' => total_rows(db_prefix() . 'contacts', 'active=0' . $where_summary),
                'contacts_last_login' => total_rows(db_prefix() . 'contacts', 'last_login LIKE "' . date('Y-m-d') . '%"' . $where_summary)
            ];
        }

        return [];
    }

    public function leads_summary()
    {
        if (has_permission('leads', '', 'view')) {
            // Leads Overview
            $leads = [];
            $leads_statuses = get_leads_summary();
    
            foreach ($leads_statuses as $key => $status) {
                $where = 'status = ' . $status['id'];
                array_push($leads, [
                    'status' => $status['name'],
                    'total' => total_rows(db_prefix() . 'leads', $where),
                    'percent' => strval(total_rows(db_prefix() . 'leads', $where) / total_rows(db_prefix() . 'leads') * 100)
                ]);
            }
            return $leads;
        }

        return [];
    }

    public function tickets_summary()
    {
        // Tickets Overview
        $tickets = [];
        $this->load->model('tickets_model');
        $tickets_statuses = $this->tickets_model->get_ticket_status();

        foreach ($tickets_statuses as $key => $status) {
            $where = 'status = ' . $status['id'];
            array_push($tickets, [
                'status' => $status['name'],
                'total' => total_rows(db_prefix() . 'tickets', $where),
                'percent' => strval(total_rows(db_prefix() . 'tickets', $where) / total_rows(db_prefix() . 'tickets') * 100)
            ]);
        }

        return $tickets;
    }
}
