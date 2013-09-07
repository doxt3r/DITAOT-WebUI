<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dita="http://dita.webnetix.co.za/" exclude-result-prefixes="dita">
	<xsl:output method="xml" indent="yes" omit-xml-declaration="no" doctype-public="-//OASIS//DTD DITA Map//EN" doctype-system="map.dtd" />
	<xsl:template match="/">
		<xsl:apply-templates select="/ul/li/ul"/>
	</xsl:template>
	
	<xsl:template match="/ul/li/ul">
		<xsl:comment>
			<xsl:text>Created with DITA OpenToolkit Web GUI ( http://dita.webnetix.co.za/ ) </xsl:text>
		</xsl:comment>
		<map title="{@dita:title}">
			<xsl:apply-templates />
		</map>
	</xsl:template>

	<xsl:template match="li">
		<topicref>
			<xsl:for-each select="@*">
				<xsl:if test="starts-with(name(),'dita:')">
				<xsl:attribute name="{local-name()}">
					<xsl:value-of select="." />
				</xsl:attribute>
				</xsl:if>
			</xsl:for-each>
			<xsl:apply-templates />
		</topicref>
	</xsl:template>
	
	<xsl:template match="text()" />
</xsl:stylesheet>