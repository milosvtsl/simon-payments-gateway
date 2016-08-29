<?php
namespace View\Home;

use View\AbstractView;


class Index extends AbstractView {

	public function renderHTMLBody()
	{
		// Render Header
		$this->getTheme()->renderHTMLBodyHeader();
?>
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
<?php
		// Render footer
		$this->getTheme()->renderHTMLBodyFooter();
	}
}

