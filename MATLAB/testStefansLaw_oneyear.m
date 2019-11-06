clc; clear; close all
% Test Stefan's Law for one point of the air temperature grid

load('/Volumes/PTV #2/rda/ice_2019/out/interpData.mat');

P = [53.15283 13.02655];

[k, dist] = dsearchn([latidueGrid(:) longitudeGrid(:)], P);

[d1, d2, ~] = size(interpT);
[row, col] = ind2sub([d1 d2], k) ;
airTSeries = squeeze(interpT(row, col, :)); 
airTSeries = airTSeries - 273.15;

% fill missing T value
newTimeVector = timeVector(1):timeVector(end);
airTSeries = interp1(timeVector, airTSeries, newTimeVector);
% if there are no value at the beggining of the time series (NaN), extrapolate
airTSeries = fillmissing(airTSeries, 'linear');


%% Get ice thickness
Tf = 0;
a = 3.3; % [cm / (C d)]^0.5

t = days(newTimeVector - newTimeVector(1));
t = t + 1; % vector starts from 0

S = (Tf - airTSeries);
S(S < 0) = 0;
h = round(a*sqrt(S.*t))/100;

%% Plot
figure; 
yyaxis left; hold on;
plot(newTimeVector, airTSeries, 'k.-');
plot(get(gca, 'XLim'), [0 0], 'b-', 'LineWidth', 2);

yyaxis right;
plot(newTimeVector, h*100, 'r.-');

axis tight;