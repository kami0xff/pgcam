<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
  xmlns:xhtml="http://www.w3.org/1999/xhtml"
  xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
<xsl:output method="html" encoding="UTF-8" indent="yes"/>
<xsl:template match="/">
<html>
<head>
  <title>
    <xsl:choose>
      <xsl:when test="sitemap:sitemapindex">Sitemap Index – PornGuru.cam</xsl:when>
      <xsl:otherwise>Sitemap – PornGuru.cam</xsl:otherwise>
    </xsl:choose>
  </title>
  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{font-family:system-ui,-apple-system,sans-serif;background:#0f0f0f;color:#e0e0e0;padding:2rem}
    h1{font-size:1.5rem;margin-bottom:.25rem;color:#fff}
    p.meta{color:#888;font-size:.85rem;margin-bottom:1.5rem}
    a{color:#a78bfa;text-decoration:none}
    a:hover{text-decoration:underline;color:#c4b5fd}
    table{width:100%;border-collapse:collapse;font-size:.85rem}
    th{background:#1a1a2e;color:#a78bfa;text-align:left;padding:.6rem .8rem;position:sticky;top:0;font-weight:600}
    td{padding:.5rem .8rem;border-bottom:1px solid #1e1e1e}
    tr:hover td{background:#161625}
    .badge{display:inline-block;padding:.15rem .5rem;border-radius:4px;font-size:.75rem;font-weight:500}
    .badge-always{background:#065f46;color:#6ee7b7}
    .badge-hourly{background:#1e3a5f;color:#7dd3fc}
    .badge-daily{background:#713f12;color:#fcd34d}
    .badge-weekly{background:#581c87;color:#d8b4fe}
    .badge-monthly{background:#3f3f46;color:#a1a1aa}
    .count{background:#1a1a2e;color:#a78bfa;padding:.4rem .8rem;border-radius:6px;font-size:.85rem;font-weight:500}
  </style>
</head>
<body>

<xsl:choose>
  <xsl:when test="sitemap:sitemapindex">
    <h1>Sitemap Index</h1>
    <p class="meta">
      <span class="count"><xsl:value-of select="count(sitemap:sitemapindex/sitemap:sitemap)"/> sitemaps</span>
    </p>
    <table>
      <tr><th>#</th><th>Sitemap</th><th>Last Modified</th></tr>
      <xsl:for-each select="sitemap:sitemapindex/sitemap:sitemap">
        <tr>
          <td><xsl:value-of select="position()"/></td>
          <td><a href="{sitemap:loc}"><xsl:value-of select="sitemap:loc"/></a></td>
          <td><xsl:value-of select="sitemap:lastmod"/></td>
        </tr>
      </xsl:for-each>
    </table>
  </xsl:when>

  <xsl:otherwise>
    <h1>Sitemap</h1>
    <p class="meta">
      <span class="count"><xsl:value-of select="count(sitemap:urlset/sitemap:url)"/> URLs</span>
    </p>
    <table>
      <tr><th>#</th><th>URL</th><th>Priority</th><th>Frequency</th><th>Last Modified</th></tr>
      <xsl:for-each select="sitemap:urlset/sitemap:url">
        <tr>
          <td><xsl:value-of select="position()"/></td>
          <td><a href="{sitemap:loc}"><xsl:value-of select="sitemap:loc"/></a></td>
          <td><xsl:value-of select="sitemap:priority"/></td>
          <td>
            <xsl:variable name="freq" select="sitemap:changefreq"/>
            <span class="badge badge-{$freq}"><xsl:value-of select="$freq"/></span>
          </td>
          <td><xsl:value-of select="sitemap:lastmod"/></td>
        </tr>
      </xsl:for-each>
    </table>
  </xsl:otherwise>
</xsl:choose>

</body>
</html>
</xsl:template>
</xsl:stylesheet>
