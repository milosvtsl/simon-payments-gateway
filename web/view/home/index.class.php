<?php
namespace View\Home;

use View\AbstractView;


class Index extends AbstractView {

	public function renderHTMLBody()
	{
?>

		<body>

		<button id="Toggletimeout" data-target="#sessionTimeoutError" data-toggle="modal" class="btn btn-success" style="display: none">Tiemout</button>
		<div id="sessionTimeoutError" class="modal fade form-horizontal pd" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog" >
				<div class="modal-content">
					<div class="modal-header">
						<button id="closeitdonotlogout" type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
						<h2 class="modal-title" > Inactivity Warning </h2>
					</div>
					<div class="modal-body">
						<p>	You will be logged out in <span id="timePeriod"></span> seconds.</p>
					</div>
					<div class="modal-body">
						<h4>Do you want to continue?</h4>
						<div class="btn-group btn-group-lg">

							<a href="#" onclick="closebox();refreshPage()" class="btn btn-info" title="Yes"><span>Yes</span></a>
							<a href="/paylogic-web/logout/index" class="btn btn-info"><span id="noButton">No</span></a>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="navbar navbar-inverse navbar-fixed-top">
			<!-- start: TOP NAVIGATION CONTAINER -->
			<div class="container">
				<div class="navbar-header">
					<!-- start: RESPONSIVE MENU TOGGLER -->
					<button data-target=".navbar-collapse" data-toggle="collapse" class="navbar-toggle" type="button">
						<span class="clip-list-2"></span>
					</button>
					<!-- end: RESPONSIVE MENU TOGGLER -->
					<!-- start: LOGO -->
					<a href="/paylogic-web/" class="navbar-brand">
						<img src="assets/images/paylogic-logo.png" alt="paylogic-web"/>
					</a>
					<!-- end: LOGO -->
				</div>

				<div class="navbar-tools">
					<!-- start: TOP NAVIGATION MENU -->
					<ul class="nav navbar-right">
						<!-- start: USER DROPDOWN -->
						<li class="dropdown current-user">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">

								<img class="nav-user-photo" src="assets/images/bg_3.png"
									 alt="User Profile Image">
							<span class="user-info">


			Welcome, Sherlock Holmes


							</span>

							</a>

							<ul class="dropdown-menu">




								<li>
									<a href="/paylogic-web/logout/index">
										<i class="fa fa-power-off"></i>
										logout
									</a>
								</li>

							</ul>
						</li>
						<!-- end: USER DROPDOWN -->
					</ul>
					<!-- end: TOP NAVIGATION MENU -->
				</div>
			</div>
			<!-- end: TOP NAVIGATION CONTAINER -->
		</div>

		<!-- Enable to overwrite Header by individual page -->


		<div id="main-container" class="main-container">
			<div class="main-container-inner">
				<!--
        This menu is used to show function that can be triggered on the content (an object or list of objects).
        -->



				<div class="navbar-content">
					<!-- start: SIDEBAR -->
					<div class="main-navigation navbar-collapse collapse">
						<!-- start: MAIN MENU TOGGLER BUTTON -->
						<div class="navigation-toggler">
							<i class="fa fa-chevron-left"></i>
							<i class="fa fa-chevron-right"></i>
						</div>

						<ul class="main-navigation-menu">

							<li class="controller active">
								<a href="/paylogic-web/home/index">
									<i class="fa fa-home"></i><span class="title">
							Home
						</span>
								</a>
							</li>

							<li class="controller">
								<a href="/paylogic-web/merchant/index">
									<i class="fa fa-user"></i><span class="title">
								Merchant
							</span>
								</a>
							</li>
							<li class="controller">
								<a href="/paylogic-web/secUser/index">
									<i class="fa fa-users"></i><span class="title">
								User
							</span>
								</a>
							</li>
							<li class="controller">
								<a href="/paylogic-web/news/editWelcomeMessage">
									<i class="fa fa-comment-o"></i><span class="title">
								Welcome Message
							</span>
								</a>
							</li>


							<li class="controller">
								<a href="/paylogic-web/news/index">
									<i class="fa fa-file-text-o"></i><span class="title">
								News
							</span>
								</a>
							</li>


							<li class="controller">
								<a href="/paylogic-web/transaction/index">
									<i class="fa fa-credit-card"></i><span class="title">
								Charge Card
							</span>
								</a>
							</li>


							<li class="controller">
								<a href="#">
									<i class="fa fa-search"></i><span class="title">
								Search
							</span>
								</a>
								<ul class="sub-menu">

									<li id="searchTransaction">
										<a href="/paylogic-web/transaction/list">Search Transaction</a>
									</li>

									<li id="searchBatch">
										<a href="/paylogic-web/batch/list">Search Batch</a>
									</li>
								</ul>
							</li>


						</ul>
						<!-- end: MAIN NAVIGATION MENU -->
					</div>
					<!-- end: SIDEBAR -->
				</div>
				<div class="main-content">
					<div class="container">



						<ul class="breadcrumb">
							<li>
								<i class="fa fa-home"></i>

								<a href="/paylogic-web/home/index">Home</a>
							</li>
							<li class="active"></li>


						</ul><!-- .breadcrumb -->
						<div class="row">
							<div class="col-xs-12">



								<div class="page-header">


									<a href="/paylogic-web/home/list" class="btn btn-purple">
										<i class="fa fa-th-list"></i>

										Home List

									</a>


									<a href="/paylogic-web/home/create" class="btn btn-success">
										<i class="fa fa-plus"></i>

										New Home

									</a>




								</div>





								<section id="intro" class="first">
									<h1>Welcome</h1>
									<p><h1>
										<big><span style="color: rgb(255, 0, 0);"><em><u><span style="font-size: 26px;">This is </span></u></em></span><span style="color: rgb(175, 238, 238);"><em><u><span style="font-size: 26px;">the</span></u></em></span><span style="color: rgb(255, 0, 0);"><em><u><span style="font-size: 26px;"> Welcome Message</span></u></em></span><span style="color: rgb(221, 160, 221);"><em><u><span style="font-size: 26px;">... Welcome!</span></u></em></span></big></h1>
									<p>
										&nbsp;</p>
									<p>
										&nbsp;</p>
									<p>
										<strong><big><span style="color: rgb(221, 160, 221);"><em><u><span style="font-size: 26px;">THIS THIS</span></u></em></span></big></strong></p>
									</p>

									<div align="right">

										<a href="/paylogic-web/home/viewLogs">View Logs</a>


									</div>

								</section>

								<br/>
								<br/>
								<br/>


								<section id="list-news" class="first">
									<table class="table table-bordered sortable">
										<thead>
										<tr>
											<th class="sortable">Description</th>
											<th class="sortable">Date</th>
										</tr>
										</thead>
										<tbody>

										<tr class="odd">
											<td><h1>
													<span style="font-size:16px;"><u><strong>Te<span style="color:#ffff00;">st</span>ing</strong></u></span></h1>
											</td>
											<td>10-06-1993</td>
										</tr>

										<tr class="even">
											<td><p>
													<span style="color:#0000ff;"><em><strong><span style="font-size:12px;">Testing 123</span></strong></em></span></p>
											</td>
											<td>10-06-1991</td>
										</tr>

										</tbody>
									</table>
								</section>


							</div>
						</div>
					</div>
				</div>
			</div>


		</div>

<?php

	}
}

