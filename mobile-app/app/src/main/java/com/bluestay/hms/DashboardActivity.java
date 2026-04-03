package com.bluestay.hms;

import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.ProgressBar;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;

import org.json.JSONObject;

import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

public class DashboardActivity extends AppCompatActivity {
    private TextView summaryText;
    private ProgressBar progress;
    private final ExecutorService executor = Executors.newSingleThreadExecutor();

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_dashboard);

        summaryText = findViewById(R.id.summaryText);
        progress = findViewById(R.id.progressDashboard);
        Button refreshBtn = findViewById(R.id.refreshBtn);
        Button roomsBtn = findViewById(R.id.roomsBtn);
        Button bookingsBtn = findViewById(R.id.bookingsBtn);
        Button inventoryBtn = findViewById(R.id.inventoryBtn);

        refreshBtn.setOnClickListener(v -> loadStats());
        roomsBtn.setOnClickListener(v -> loadSimple("rooms.list"));
        bookingsBtn.setOnClickListener(v -> loadSimple("bookings.list"));
        inventoryBtn.setOnClickListener(v -> loadSimple("inventory.list"));

        loadStats();
    }

    private void loadStats() {
        progress.setVisibility(View.VISIBLE);
        summaryText.setText("Loading dashboard stats...");
        executor.execute(() -> {
            try {
                JSONObject json = LoginActivity.apiClient.getJson("dashboard.stats");
                JSONObject data = json.getJSONObject("data");
                String text = "Users: " + data.optInt("users") +
                        "\nRooms: " + data.optInt("rooms") +
                        "\nBookings: " + data.optInt("bookings") +
                        "\nOpen Requests: " + data.optInt("open_requests") +
                        "\nRevenue: " + data.optDouble("total_revenue");
                runOnUiThread(() -> {
                    progress.setVisibility(View.GONE);
                    summaryText.setText(text);
                });
            } catch (Exception e) {
                runOnUiThread(() -> {
                    progress.setVisibility(View.GONE);
                    summaryText.setText("Failed: " + e.getMessage());
                });
            }
        });
    }

    private void loadSimple(String action) {
        progress.setVisibility(View.VISIBLE);
        summaryText.setText("Loading " + action + "...");
        executor.execute(() -> {
            try {
                JSONObject json = LoginActivity.apiClient.getJson(action);
                int count = json.getJSONArray("data").length();
                runOnUiThread(() -> {
                    progress.setVisibility(View.GONE);
                    summaryText.setText(action + " count: " + count);
                });
            } catch (Exception e) {
                runOnUiThread(() -> {
                    progress.setVisibility(View.GONE);
                    summaryText.setText("Failed: " + e.getMessage());
                });
            }
        });
    }
}
