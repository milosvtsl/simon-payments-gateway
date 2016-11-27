<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/29/2016
 * Time: 1:25 PM
 */
namespace Support\View;

use Support\Model\SupportTicketRow;
use View\AbstractView;

class SupportTicketView extends AbstractView
{
    const VIEW_PATH = 'support/ticket';
    const VIEW_NAME = 'Support Tickets';

    private $ticket;
    private $action;

    public function __construct($uid, $action=null) {
        $this->action = $action ?: 'view';
        $this->ticket = SupportTicketRow::fetchByUID($uid);
        if(!$this->ticket)
            throw new \InvalidArgumentException("Invalid Support Ticket UID: " . $uid);
        parent::__construct();
    }

    /** @return SupportTicketRow */
    public function getTicket() { return $this->ticket; }

    public function renderHTMLBody(Array $params) {

        // Render Page
        switch($this->action) {
            case 'view':
                include('.view.php');
                break;
            case 'edit':
                include('.edit.php');
                break;
            default:
                throw new \InvalidArgumentException("Invalid Action: " . $this->action);
        }
    }

    public function processFormTicket(Array $post) {
        try {
            // Render Page
            switch($this->action) {
                case 'delete':
                    print_r($post);
                    die();
                    break;
                case 'change':
                    print_r($post);
                    die();
                    break;
                default:
                    throw new \InvalidArgumentException("Invalid Action: " . $this->action);
            }

        } catch (\Exception $ex) {
            $this->setSessionMessage($ex->getMessage());
            header('Location: /support/ticket?id=' . $this->getTicket()->getID() . '&action=edit&message=Unable to manage batch: ' . $ex->getMessage());

            die();
        }
    }
}