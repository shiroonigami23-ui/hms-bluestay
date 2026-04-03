package com.bluestay.hms;

import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.net.CookieHandler;
import java.net.CookieManager;
import java.net.HttpURLConnection;
import java.net.URI;
import java.net.URL;
import java.net.URLEncoder;
import java.nio.charset.StandardCharsets;

public class ApiClient {
    private static final String BASE_URL = "https://shirooni.free.nf";
    private final CookieManager cookieManager;

    public ApiClient() {
        cookieManager = new CookieManager();
        CookieHandler.setDefault(cookieManager);
    }

    public String getCsrfToken(String path) throws Exception {
        HttpURLConnection con = (HttpURLConnection) new URL(BASE_URL + path).openConnection();
        con.setRequestMethod("GET");
        con.setRequestProperty("User-Agent", "BlueStayNative/1.0");
        String body = readBody(con);
        con.disconnect();
        String marker = "name=\"_csrf_token\" value=\"";
        int idx = body.indexOf(marker);
        if (idx >= 0) {
            int start = idx + marker.length();
            int end = body.indexOf("\"", start);
            if (end > start) return body.substring(start, end);
        }
        String meta = "<meta name=\"csrf-token\" content=\"";
        int idx2 = body.indexOf(meta);
        if (idx2 >= 0) {
            int start = idx2 + meta.length();
            int end = body.indexOf("\"", start);
            if (end > start) return body.substring(start, end);
        }
        return "";
    }

    public boolean login(String email, String password) throws Exception {
        String csrf = getCsrfToken("/login.php");
        String data = "email=" + enc(email) + "&password=" + enc(password) + "&_csrf_token=" + enc(csrf);
        HttpURLConnection con = (HttpURLConnection) new URL(BASE_URL + "/login.php").openConnection();
        con.setRequestMethod("POST");
        con.setRequestProperty("Content-Type", "application/x-www-form-urlencoded");
        con.setRequestProperty("User-Agent", "BlueStayNative/1.0");
        con.setDoOutput(true);
        try (OutputStream os = con.getOutputStream()) {
            os.write(data.getBytes(StandardCharsets.UTF_8));
        }
        int code = con.getResponseCode();
        con.disconnect();
        return code == 200 || code == 302;
    }

    public JSONObject getJson(String action) throws Exception {
        HttpURLConnection con = (HttpURLConnection) new URL(BASE_URL + "/api.php?action=" + URI.create(action).toString()).openConnection();
        con.setRequestMethod("GET");
        con.setRequestProperty("User-Agent", "BlueStayNative/1.0");
        String body = readBody(con);
        con.disconnect();
        return new JSONObject(body);
    }

    private String readBody(HttpURLConnection con) throws Exception {
        BufferedReader br;
        if (con.getResponseCode() >= 400) {
            br = new BufferedReader(new InputStreamReader(con.getErrorStream(), StandardCharsets.UTF_8));
        } else {
            br = new BufferedReader(new InputStreamReader(con.getInputStream(), StandardCharsets.UTF_8));
        }
        StringBuilder sb = new StringBuilder();
        String line;
        while ((line = br.readLine()) != null) {
            sb.append(line);
        }
        br.close();
        return sb.toString();
    }

    private String enc(String v) {
        return URLEncoder.encode(v, StandardCharsets.UTF_8);
    }
}
