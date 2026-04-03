package com.bluestay.hms;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ProgressBar;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;

import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

public class LoginActivity extends AppCompatActivity {
    private EditText emailInput;
    private EditText passwordInput;
    private TextView statusText;
    private ProgressBar progressBar;
    private final ExecutorService executor = Executors.newSingleThreadExecutor();
    public static ApiClient apiClient = new ApiClient();

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);

        emailInput = findViewById(R.id.emailInput);
        passwordInput = findViewById(R.id.passwordInput);
        statusText = findViewById(R.id.statusText);
        progressBar = findViewById(R.id.progressBar);
        Button loginButton = findViewById(R.id.loginButton);

        emailInput.setText("admin@bluestay.local");
        passwordInput.setText("Password@123");

        loginButton.setOnClickListener(v -> doLogin());
    }

    private void doLogin() {
        final String email = emailInput.getText().toString().trim();
        final String password = passwordInput.getText().toString();
        if (email.isEmpty() || password.isEmpty()) {
            statusText.setText("Enter email and password.");
            return;
        }

        progressBar.setVisibility(View.VISIBLE);
        statusText.setText("Signing in...");

        executor.execute(() -> {
            try {
                boolean ok = apiClient.login(email, password);
                runOnUiThread(() -> {
                    progressBar.setVisibility(View.GONE);
                    if (ok) {
                        statusText.setText("Login success");
                        startActivity(new Intent(LoginActivity.this, DashboardActivity.class));
                    } else {
                        statusText.setText("Login failed. Check credentials.");
                    }
                });
            } catch (Exception e) {
                runOnUiThread(() -> {
                    progressBar.setVisibility(View.GONE);
                    statusText.setText("Error: " + e.getMessage());
                });
            }
        });
    }
}
