clc; clear; close all
% Test Stefan's Law for one point of the air temperature grid

a = 3.3; % [cm / (C d)]^0.5

%% Example 1 from Lepparants (1993)
data = [1 -10; 5 -10; 10 -10;  50 -10; 100 -10; 150 -20; 200 -20];

Tf = 0;
t = data(:, 1);
h = round(a*sqrt((Tf - data(:, 2)).*t))

%% Example 2 with increasing T
data = [1 -10; 5 -10; 10 -10;  50 -10; 100 1; 150 10; 200 20];

Tf = 0;
t = data(:, 1);
S = (Tf - data(:, 2));
S(S < 0) = 0;
h = round(a*sqrt(S.*t))