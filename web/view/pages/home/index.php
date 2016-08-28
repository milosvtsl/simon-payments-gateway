<%@ page import="java.util.TimeZone" %>
<%@ page import="com.paylogic.web.security.SecUser" %>
<!doctype html>
<html>
<head>
    <title><g:message code="default.welcome.title" args="[meta(name:'app.name')]"/> </title>
    <meta name="layout" content="main" />
    <script type="text/javascript">
        $(document).ready(function(){
            $(".page-header").hide();
        });
    </script>
</head>

<body>

	<section id="intro" class="first">
		<h1>Welcome</h1>
		<p>${message}</p>
		 <%--start fix for transaction logger--%>
		<div align="right">
			<sec:ifAllGranted roles="ROLE_ADMIN">
			 <g:link  action="viewLogs" controller="home" >View Logs</g:link>
			 </sec:ifAllGranted>
			<sec:ifNotGranted roles="ROLE_ADMIN">
		
			</sec:ifNotGranted>
		</div>
		<%--end fix for transaction logger--%>
	</section>

	<br/>
	<br/>
	<br/>

	<g:if test="${newsCount > 0}">
		<section id="list-news" class="first">
			<table class="table table-bordered sortable">
				<thead>
					<tr>
						<th class="sortable"><g:message code="default.description.label"/></th>
						<th class="sortable"><g:message code="default.date.label"/></th>
					</tr>
				</thead>
				<tbody>
					<g:each in="${newsList}" status="i" var="instance">
						<tr class="${(i % 2) == 0 ? 'odd' : 'even'}">
							<td>${instance.description}</td>
							<td><g:formatDate format="MM-dd-yyyy" date="${instance.date}" timezone="${TimeZone.getTimeZone(timeZone)}"/></td>
						</tr>
					</g:each>
				</tbody>
			</table>
		</section>
	</g:if>
	<g:else>
		<p><em><g:message code="no.news.for.merchant"/></em></p>
	</g:else>
</body>
</html>
