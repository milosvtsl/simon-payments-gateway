<?php
namespace View\Home;

use View\AbstractView;


class Index extends AbstractView {

	public function renderHTMLBody(Array $params)
	{
		// Render Header
		$this->getTheme()->renderHTMLBodyHeader();
?>
			<section id="intro" class="first">
				<h1>Welcome</h1>
				<h5>Under Construction...</h5>
			</section>
<?php
		// Render footer
		$this->getTheme()->renderHTMLBodyFooter();
	}
}

