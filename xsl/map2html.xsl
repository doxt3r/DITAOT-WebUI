<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dita="http://dita.webnetix.co.za/">
	<xsl:output method="xml" indent="yes" omit-xml-declaration="yes" />
	
	<xsl:template match="/">
		<xsl:apply-templates />
	</xsl:template>
	
	<xsl:template match="*[contains( @class , ' map/map ')]">
		<ul>
			<li><xsl:value-of select="@title" />
			<ul id="list-container" dita:title="{@title}">
			<xsl:apply-templates />
			</ul>
			</li>
		</ul>
	</xsl:template>

	<xsl:template match="*[contains( @class , ' map/topicref ')]">
		<xsl:variable name="id">
			<xsl:call-template name="getId">
				<xsl:with-param name="href" select="@href" />
			</xsl:call-template>
		</xsl:variable>

		<xsl:variable name="file">
			<xsl:call-template name="getFile">
				<xsl:with-param name="href" select="@href" />
			</xsl:call-template>
		</xsl:variable>
		
		<xsl:variable name="topicTitle">
			<xsl:call-template name="getTitle">
				<xsl:with-param name="file" select="$file" />
				<xsl:with-param name="id" select="$id" />
				<xsl:with-param name="navtitle" select="@navtitle" />
			</xsl:call-template>
		</xsl:variable>
		
		<li class="sortable-element-class">
			<xsl:attribute name="id">
				<xsl:value-of select="$id" />
			</xsl:attribute>
			<xsl:attribute name="dita:navtitle">
				<xsl:value-of select="@navtitle" />
			</xsl:attribute>
			<xsl:attribute name="dita:href">
				<xsl:value-of select="@href" />
			</xsl:attribute>

			<xsl:choose>
				<xsl:when test="*[contains( @class , ' map/topicref ')]">
					<xsl:value-of select="$topicTitle" />
					<ul>
						<xsl:apply-templates select="*[contains( @class , ' map/topicref ')]"/>
					</ul>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="$topicTitle" />
				</xsl:otherwise>
			</xsl:choose>
			<!-- <xsl:apply-templates /> -->
		</li>
	</xsl:template>
	
	<xsl:template name="getId">
		<xsl:param name="href" />
		<xsl:choose>
			<xsl:when test="contains( $href, '#')">
				<xsl:value-of select="substring-after( $href, '#')" />
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="generate-id()" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template name="getFile">
		<xsl:param name="href" />
		<xsl:choose>
			<xsl:when test="contains( $href, '#')">
				<xsl:value-of select="substring-before( $href, '#')" />
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$href" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template name="getTitle">
		<xsl:param name="file" />
		<xsl:param name="id" />
		<xsl:param name="navtitle" />
		<xsl:choose>
			<xsl:when test="$navtitle and $navtitle != ''">
				<xsl:value-of select="$navtitle" />
			</xsl:when>
			<xsl:when test="document($file, .)//*[@id = $id]/*[contains( @class, ' topic/title' )]">
				<xsl:value-of select="document($file, .)//*[@id = $id]/*[contains( @class, ' topic/title' )]" />
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="document($file, .)//*[contains( @class , ' topic/topic ')]/*[contains( @class, ' topic/title' )]" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>